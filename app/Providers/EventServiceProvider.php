<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Models\Employee;
use App\Models\LoginLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
            $messageId = $event->auth->getLastMessageId();
            $samlUser = $event->auth->getSaml2User();
            $attributes = $samlUser->getAttributes();

            if(isset($attributes['username']) && is_array($attributes['username']) && str_starts_with($attributes['username'][0],'n')){
                if($employee = Employee::where('remoteId',substr($attributes['username'][0],1))->first()){
                    LoginLog::create([
                        'remoteId' => $employee->remoteId,
                        'firstname' => $employee->firstname,
                        'lastname' => $employee->lastname,
                        'logged_in_at' => now(),
                        'ip_address' => request()->ip(),
                    ]);
                    abort(redirect('https://intern.rkhl.at?token='.$employee->createToken("API TOKEN")->plainTextToken));
                    exit;
                }
            }
            if(isset($attributes['mail']) && is_array($attributes['mail'])){
                if($employee = Employee::where('email',$attributes['mail'][0])->first()){
                    LoginLog::create([
                        'remoteId' => $employee->remoteId,
                        'firstname' => $employee->firstname,
                        'lastname' => $employee->lastname,
                        'logged_in_at' => now(),
                        'ip_address' => request()->ip(),
                    ]);
                    abort(redirect('https://intern.rkhl.at?token='.$employee->createToken("API TOKEN")->plainTextToken));
                    exit;
                }
            }
            if(isset($attributes['mnr']) && is_array($attributes['mnr'])){
                if($employee = Employee::where('remoteId',$attributes['mnr'][0])->first()){
                    LoginLog::create([
                        'remoteId' => $employee->remoteId,
                        'firstname' => $employee->firstname,
                        'lastname' => $employee->lastname,
                        'logged_in_at' => now(),
                        'ip_address' => request()->ip(),
                    ]);
                    abort(redirect('https://intern.rkhl.at?token='.$employee->createToken("API TOKEN")->plainTextToken));
                    exit;
                }
            }
            abort(redirect('https://intern.rkhl.at'));
			exit;
        });
    }
}
