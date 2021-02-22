<?php

namespace App\Repositories;

use App\Account;
use App\Models\SiteAccessLog;
use App\User;
use App\UserLogin;
use Illuminate\Support\Facades\Auth;

class AdminDashboardMetrics
{
    /** @var User */
    private $loggedInUser;

    private $activeTrenchDevUsers = 0;
    private $userLoginsPastMonth = 0;
    private $pageVisitors = 0;
    private $myPortfolioVisits = 0;

    /**
     * AdminDashboardMetrics constructor.
     */
    public function __construct()
    {
        $this->loggedInUser = Auth::user();
        $this->initialize();
    }


    private function initialize()
    {
        // we can cache these values in the future
        $this->activeTrenchDevUsers = User::query()
            ->where('is_active', 1)
            ->where('account_id', Account::getTrenchDevsAccount()->id)
            ->count();

        $lastMonth = date('Y-m-d H:i:s', strtotime('-1 month'));

        $this->userLoginsPastMonth = UserLogin::query()
            ->where('created_at', '>=', $lastMonth)
            ->where('type', '=', UserLogin::DB_TYPE_LOGIN)
            ->count();

        $this->pageVisitors = SiteAccessLog::query()
            ->where('url', get_site_url())
            ->where('created_at', '>=', $lastMonth)
            ->count();

        $this->myPortfolioVisits = SiteAccessLog::query()
            ->where('url', $this->loggedInUser->getPortfolioUrl())
            ->count();
    }

    /**
     * @return int
     */
    public function getActiveTrenchDevUsers(): int
    {
        return $this->activeTrenchDevUsers;
    }

    /**
     * @return int
     */
    public function getUserLoginsPastMonth(): int
    {
        return $this->userLoginsPastMonth;
    }

    /**
     * @return int
     */
    public function getPageVisitors(): int
    {
        return $this->pageVisitors;
    }

    /**
     * @return mixed
     */
    public function getMyPortfolioVisits(): int
    {
        return $this->myPortfolioVisits;
    }
}
