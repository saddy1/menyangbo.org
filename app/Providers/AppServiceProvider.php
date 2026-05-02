<?php

namespace App\Providers;
use App\Models\Feedback;
use App\Models\PersonChangeRequest;
use App\Support\NepaliCalendar;
use App\Support\SiteRenewal;
use Illuminate\Support\Facades\View;
use App\Models\Admin;

use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         View::composer('admin.layout', function ($view) {
            $pendingRequests = PersonChangeRequest::where('status', 'pending')->count();
            $unreadFeedback = Feedback::whereNull('read_at')->count();

            $view->with('admin', Admin::find(session('admin_id')))
                ->with('pendingRequestsCount', $pendingRequests)
                ->with('unreadFeedbackCount', $unreadFeedback)
                ->with('adminNotificationCount', $pendingRequests + $unreadFeedback)
                ->with('renewDate', SiteRenewal::date())
                ->with('renewDaysLeft', SiteRenewal::daysLeft())
                ->with('siteExpired', SiteRenewal::isExpired());
        });

        View::composer('layouts.app', function ($view) {
            $view->with('calendarToday', NepaliCalendar::today())
                ->with('siteExpired', SiteRenewal::isExpired())
                ->with('renewDate', SiteRenewal::date());
        });
    }
}
