<?php


namespace Packages\ShaunSocial\PaidContent\Repositories\Api;

use Exception;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;
use Packages\ShaunSocial\Core\Repositories\Api\SubscriptionRepository;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Support\Facades\Subscription;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;
use Packages\ShaunSocial\PaidContent\Enum\SubscriberPackageType;
use Packages\ShaunSocial\PaidContent\Enum\UserPostPaidOrderStatus;
use Packages\ShaunSocial\PaidContent\Http\Resources\SubscriberPackageResource;
use Packages\ShaunSocial\PaidContent\Http\Resources\TipPackageResource;
use Packages\ShaunSocial\PaidContent\Http\Resources\UserSubscriberDetailResource;
use Packages\ShaunSocial\PaidContent\Http\Resources\UserSubscriberPackageResource;
use Packages\ShaunSocial\PaidContent\Http\Resources\UserSubscriberResource;
use Packages\ShaunSocial\PaidContent\Models\SubscriberPackage;
use Packages\ShaunSocial\PaidContent\Models\SubscriberTrial;
use Packages\ShaunSocial\PaidContent\Models\TipPackage;
use Packages\ShaunSocial\PaidContent\Models\UserPostPaid;
use Packages\ShaunSocial\PaidContent\Models\UserPostPaidOrder;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriberPackage;
use Packages\ShaunSocial\PaidContent\Notification\PaidContentBuyPostNotification;
use Packages\ShaunSocial\Wallet\Http\Resources\WalletTransactionResource;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\Wallet\Notification\WalletTipNotification;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class PaidContentRepository
{   
    use CacheSearchPagination;

    protected $subscriptionRepository;
    protected $postRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository, PostRepository $postRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->postRepository = $postRepository;
    }

    public function store_paid_post($id, $refCode, $viewer)
    {
        $post = Post::findByField('id', $id);

        $owner = $post->getUser();
        
        DB::beginTransaction();
        $result = ['status' => false];
        try {
            list($amount, $fee, $amountRef) = getAmountOfPaidContent($post->content_amount, [$viewer->id, $post->user_id], $refCode);
            
            $order = UserPostPaidOrder::create([
                'user_id' => $viewer->id,
                'post_id' => $id,
                'post_owner_id' => $post->user_id,
                'amount' => $post->content_amount,
                'status' => UserPostPaidOrderStatus::DONE,
                'gateway_id' => config('shaun_gateway.wallet_id'),
                'params' => json_encode([
                    'fee' => $fee + $amountRef
                ]),
                'currency' => getWalletTokenName()
            ]);

            $userResult = Wallet::transfer('paid_content', $viewer->id, $post->user_id, $post->content_amount, $order, $order, 'paid_content_buy_post',[
                'fee' => $fee + $amountRef
            ]);
            $systemResult = ['status' => true];
            if ($fee) {
                $systemResult = Wallet::add('payment', config('shaun_wallet.system_wallet_user_id'), $fee, $order, 'root_buy_post_fee', $viewer->id);
            }
            $refResult = ['status' => true];
            if ($amountRef) {
                $userRef = User::findByField('ref_code', $refCode);
                $refResult = Wallet::add('commission', $userRef->id, $amountRef, $order, 'paid_content_buy_post', config('shaun_wallet.system_wallet_user_id'));
            }
            if ($userResult['status'] && $systemResult['status'] && $refResult['status']) {
                UserPostPaid::create([
                    'user_id' => $viewer->id,
                    'post_id' => $id,
                    'order_id' => $order->id
                ]);
                $post->update([
                    'earn_amount' => $post->earn_amount + $post->content_amount
                ]);
                $owner->update([
                    'earn_amount' => $owner->earn_amount + $post->content_amount,
                    'earn_fee_amount' => $owner->earn_fee_amount + $fee + $amountRef,
                ]);
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        if ($result['status'] && $owner->checkNotifySetting('new_ppv_purchase')) {
            Notification::send($owner, $viewer, PaidContentBuyPostNotification::class, $post, [], 'shaun_paid_content');
        }
        
        return $result;
    }

    public function get_config($viewer)
    {
        return [
            'check_profile' => $viewer->checkProfileForPaidContent(),
            'check_verify' => $viewer->checkVerifyForPaidContent(),
            'check_membership' => $viewer->checkSubscriptionForPaidContent(),
            'check_price' => $viewer->checkPriceForPaidContent() ? true : false
        ];
    }

    public function store_subscriber_user($id, $refCode, $viewer)
    {
        $userPackage = UserSubscriberPackage::findByField('id', $id);

        $package = $userPackage->getPackage();
        $user = $userPackage->getUser();
        if ($user->paid_content_trial_day && ! SubscriberTrial::getSubscriberTrial($viewer->id, $user->id)) {
            $package->setTrial($user->paid_content_trial_day);
        }

        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $subscription = Subscription::create($viewer, 'user_subscriber', getWalletTokenName(), config('shaun_gateway.wallet_id'), $user, $package, true, ['ref_code' => $refCode]);
            if ($subscription->trial_day) {
                $subscription->doActive([], false);
                SubscriberTrial::create([
                    'user_id' => $viewer->id,
                    'subscriber_id' => $user->id
                ]);
                $result['status'] = true;
                DB::commit();
            } else {
                $userResult = Wallet::transferSubscription($subscription);
                if ($userResult['status']) {
                    $transaction = $userResult['from_transaction'];
                    $subscription->doActive([], true, $package->amount, $transaction->getId());
                    $result['status'] = true;
                    DB::commit();
                } else {
                    $result['message'] = __("You don't have enough balance");
                    DB::rollBack();
                }
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }
        
        return $result;
    }

    public function get_packages($viewer)
    {
        $packages = SubscriberPackage::getAll();
        $userPackages = UserSubscriberPackage::findByField('user_id', $viewer->id, true);
        $userPackages = $userPackages->filter(function ($value, int $key) {
            return $value->getPackage()->canSubscriber();
        });
        
        $result = [
            'all' => [
                'monthly' => [],
                'quarterly' => [],
                'biannual' => [],
                'annual' => []
            ],
            'current' => [
                'monthly' => 0,
                'quarterly' => 0,
                'biannual' => 0,
                'annual' => 0
            ],
            'paid_content_trial_day' => $viewer->paid_content_trial_day,
            'subscriber_count' => $viewer->subscriber_count
        ];

        foreach ($packages as $package) {
            $result['all'][$package->type->value][] = new SubscriberPackageResource($package);
        }
        $default = '';
        foreach ($userPackages as $userPackage) {
            $package = $userPackage->getPackage();
            $result['current'][$package->type->value] = $package->id;
            if ($userPackage->is_default) {
                $default = $package->type->value;
            }
        }

        $result['default'] = $default;

        return $result;
    }

    public function get_profile_packages($id, $viewer)
    {
        $user = User::findByField('id', $id);
        $trial = 0;
        if ($user->paid_content_trial_day && ! SubscriberTrial::getSubscriberTrial($viewer->id, $id)) {
            $trial = $user->paid_content_trial_day;
        }
        $userPackages = UserSubscriberPackage::findByField('user_id', $id, true);
        $userPackages = $userPackages->filter(function ($value, int $key) use ($trial) {
            $package = $value->getPackage();
            $package->setTrial($trial);
            return $package->canSubscriber();
        });

        $features = [];
        $posts = Post::getPostPaidFeatures($id);
        $features = $posts->map(function ($item, $key) {
            return $item->getThumb();
        });

        return [
            'post_paid_count' => $user->post_paid_count,
            'subscriber_count' => $user->subscriber_count,
            'packages' => UserSubscriberPackageResource::collection($userPackages),
            'features' => $features
        ];
    }

    public function get_earning_transaction($viewer)
    {
        $builder = WalletTransaction::where('user_id', $viewer->id)->where('type', 'paid_content')->where('amount', '>', 0)->orderBy('id', 'DESC');
        $transactions = $this->getCacheSearchPagination('earning_transaction_'. $viewer->id,$builder, 1);

        return WalletTransactionResource::collection($transactions);
    }

    public function get_earning_report($viewer)
    {
        return [
            'total' => formatNumber($viewer->earn_amount),
            'fee' => formatNumber($viewer->earn_fee_amount),
            'earning' => formatNumber($viewer->earn_amount - $viewer->earn_fee_amount)
        ];
    }

    public function store_user_package($data, $viewer)
    {
        $packages = UserSubscriberPackage::findByField('user_id', $viewer->id, true);
        $packages->each(function($package) {
            $package->delete();
        });
        
        $default = $data['default'] ?? '';

        if (! empty($data['monthly_id'])) {
            UserSubscriberPackage::create([
                'package_id' => $data['monthly_id'],
                'user_id' => $viewer->id,
                'is_default' => $default == SubscriberPackageType::MONTHLY->value ? true : 0
            ]);
        }

        if (! empty($data['quarterly_id'])) {
            UserSubscriberPackage::create([
                'package_id' => $data['quarterly_id'],
                'user_id' => $viewer->id,
                'is_default' => $default == SubscriberPackageType::QUARTERLY->value ? true : 0
            ]);
        }

        if (! empty($data['biannual_id'])) {
            UserSubscriberPackage::create([
                'package_id' => $data['biannual_id'],
                'user_id' => $viewer->id,
                'is_default' => $default == SubscriberPackageType::BIANNUAL->value ? true : 0
            ]);
        }

        if (! empty($data['annual_id'])) {
            UserSubscriberPackage::create([
                'package_id' => $data['annual_id'],
                'user_id' => $viewer->id,
                'is_default' => $default == SubscriberPackageType::ANNUAL->value ? true : 0
            ]);
        }

        if (isset($data['paid_content_trial_day'])) {
            $viewer->update([
                'paid_content_trial_day' => $data['paid_content_trial_day']
            ]);
        }
    }

    public function get_user_subscriber($data, $viewer)
    {
        $page = $data['page'] ?? 1;
        $builder = UserSubscriber::select('user_subscribers.*')->where('subscriber_id', $viewer->id)->orderBy('user_subscribers.id', 'DESC');
        if (! empty($data['keyword'])) {
            $name = $data['keyword'];
            $builder->join('users', function ($join) use ($name) {
                $join->on('users.id', '=', 'user_subscribers.user_id')->where(function ($query) use ($name){
                    $query->where('users.name', 'LIKE', '%'.$name.'%')->orWhere('users.user_name', 'LIKE', '%'.$name.'%');
                });
            });
        }

        if ($data['status'] != 'all') {
            $status = $data['status'];
            $builder->join('subscriptions', function ($join) use ($status) {
                $join->on('subscriptions.id', '=', 'user_subscribers.subscription_id')->where(function ($query) use ($status){
                    $query->where('subscriptions.status', $status);
                });
            });
        }
        switch ($data['date_type']) {
            case '30_day':
                $builder->where('user_subscribers.created_at', '>=', now()->subDays(30));
                break;
            case '60_day':
                $builder->where('user_subscribers.created_at', '>=', now()->subDays(60));
                break;
            case '90_day':
                $builder->where('user_subscribers.created_at', '>=', now()->subDays(90));
                break;
            case 'custom':
                if ($data['from_date']) {
                    $builder->where('user_subscribers.created_at', '>=', $data['from_date']. ' 00:00:00');
                }
                if ($data['to_date']) {
                    $builder->where('user_subscribers.created_at', '<=', $data['to_date']. ' 23:59:59');
                }
                break;
        }
        unset($data['page']);
        $key = md5(json_encode($data));

        $subscribers = $this->getCacheSearchPagination($key, $builder, $page, 0, config('shaun_core.cache.time.short'));
        $subscribersNextPage = $this->getCacheSearchPagination($key, $builder, $page + 1, 0, config('shaun_core.cache.time.short'));

        return [
            'items' => UserSubscriberResource::collection($subscribers),
            'has_next_page' => count($subscribersNextPage) ? true : false
        ];
    }

    public function get_subscriber_detail($id)
    {
        $subscriber = UserSubscriber::findByField('id', $id);

        return new UserSubscriberDetailResource($subscriber);
    }

    public function store_subscriber_cancel($id)
    {
        $subscriber = UserSubscriber::findByField('id', $id);
        $subscription = $subscriber->getSubscription();
        $subscription->doCancel();
        $subscription->update([
            'admin_cancel' => true
        ]);
    }

    public function store_subscriber_resume($id)
    {
        $subscriber = UserSubscriber::findByField('id', $id);
        $subscription = $subscriber->getSubscription();
        $subscription->doResume();
        $subscription->update([
            'admin_cancel' => false
        ]);
    }

    public function get_subscriber_transaction($id, $page)
    {
        $subscriber = UserSubscriber::findByField('id', $id);

        return $this->subscriptionRepository->get_transaction($subscriber->subscription_id, $page);
    }

    public function get_tip_packages()
    {
        $packages = TipPackage::getAll();

        return TipPackageResource::collection($packages);
    }

    public function store_tip($data, $viewer)
    {
        $amount = $data['amount'] ?? 0;
        if (! empty($data['package_id'])) {
            $package = TipPackage::findByField('id', $data['package_id']);
            $amount = $package->amount;
        }

        $result = ['status' => false];
        $user = User::findByField('id', $data['user_id']);
        try {
            $walletResult = Wallet::transfer('payment', $viewer->id, $data['user_id'], $amount, $user, $viewer, 'tip');
            if ($walletResult['status']) {
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        if ($result['status']) {
            Notification::send($user,$user, WalletTipNotification::class, $viewer, ['is_system' => true], 'shaun_wallet', false, false);
        }

        return $result;
    }

    public function get_my_paid_post($page, $viewer)
    {
        $builder = UserPostPaid::where('user_id', $viewer->id)->orderBy('id', 'DESC');
        $results = UserPostPaid::getCachePagination('user_post_paid_'.$viewer->id, $builder, $page);
        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push($post);
            }
        }

        $posts = $this->postRepository->filterPostList($posts, $viewer, true);

        return PostResource::collection($posts);
    }

    public function get_profile_paid_post($id, $page, $viewer)
    {
        $posts = Post::getCachePagination('user_profile_paid_content_'.$id, Post::where('user_id', $id)->where('is_paid', true)->orderBy('id', 'DESC'), $page);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function store_edit_post($data)
    {
        $post = Post::findByField('id', $data['id']);
        if (isset($data['thumb_file_id']) && !$data['thumb_file_id']) {
            unset($data['thumb_file_id']);
        }
        if (empty($data['thumb_file_id']) && !empty($data['thumb_delete'])) {
            $data['thumb_file_id'] = 0;
        }
        $post->update($data);

        if (!empty($data['thumb_file_id'])) {
            $file = StorageFile::findByField('id', $data['thumb_file_id']);
            $file->update([
                'parent_id' => $post->id
            ]);
        }

        $post->makeThumbPurre();

        return new PostResource($post);
    }
}
