<?php


namespace Packages\ShaunSocial\Core\Http\Middleware;

use Illuminate\Http\Request;
class restrictIpAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        if (alreadyInstalled()) {
            if (setting('spam.ip_ban') && ! $request->is(env('APP_ADMIN_PREFIX', 'admin').'/*')) {                
                $list = explode(',', setting('spam.ip_ban'));
                if (! empty($list)) {
                    $ipCurrent = ip2long($_SERVER['REMOTE_ADDR']);
                    $banned = false;
                    $bannedIpList = [];
                    $bannedIpRanges = [];

                    foreach( $list as $ip ) {
                        $ip = trim($ip);
                        if( strpos($ip, '*') !== false ) {
                            $low  = ip2long(str_replace('*', '0', $ip));
                            $high = ip2long(str_replace('*', '255', $ip));

                            if(!$low || !$high || $low > $high) {
                                continue;
                            }

                            $bannedIpRanges[] = [
                                $low,
                                $high
                            ];
                        }
                        else
                        {
                            $tmp = ip2long($ip);
                            if( $tmp )
                            {
                                $bannedIpList[] = $tmp;
                            }
                        }
                    }

                    if(in_array($ipCurrent, $bannedIpList)) {
                        $banned = true;
                    } else {
                        foreach($bannedIpRanges as $range) {
                            if($ipCurrent >= $range[0] && $ipCurrent <= $range[1]) {
                                $banned = true;
                                break;
                            }
                        }
                    }
                    
                    if ($banned) {
                        abort(405, __('You are not allowed to access this site.'));
                    }
                }
            }
        }

        return $next($request);
    }
}
