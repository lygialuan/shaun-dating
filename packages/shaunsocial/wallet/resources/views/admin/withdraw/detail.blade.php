<header class="modal-card-head">
    <p class="modal-card-title">{{__('Payment request')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <p>{{__('Request amount')}}: {{$withdraw->getAmountInfo(). ' ('.$withdraw->getExchangeInfo().')'}}</p>
        <p>{{__('Fee')}}: {{$withdraw->getFee(true)}}</p>
        <p>{{__('Net amount')}}: {{$withdraw->getNet(true)}}</p>
        <p>{{__('Currency')}}: {{$withdraw->currency}}</p>
        <p>{{__('Method')}}: {{$withdraw->getPaymentMethod()}}</p>
        <p>{{__('Account detail')}}: {!! nl2br(e($withdraw->bank_account)) !!}</p>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-white modal-close">{{__('Close')}}</button>
</footer>