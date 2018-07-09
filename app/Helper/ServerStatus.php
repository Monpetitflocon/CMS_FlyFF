<?php

namespace App\Helper;

use App\Model\Character\Character;
use App\Model\Character\CombatInfo;
use App\Model\Character\MultiServerInfo;
use App\Model\Logging\UserCount;
use App\Model\Web\Setting;

class ServerStatus
{
    /** @var string */
    public $status = '-';

    /** @var int */
    public $accounts_number = 0;

    /** @var int */
    public $players_number = 0;

    /** @var int */
    public $connected_number = 0;

    /** @var int */
    public $max_connected_number = 0;

    /** @var string */
    public $exp_rate = '-';

    /** @var string */
    public $drop_rate = '-';

    /** @var string */
    public $penyas_rate = '-';

    /** @var string */
    public $mvp_info = '-';

    /** @var string */
    public $gs_info = '-';

    /** @var string */
    public $lord_info = '-';

    /** @var string */
    public $event_info = '-';

    /**
     * ServerStatus constructor.
     */
    public function __construct()
    {
        $settings = Setting::getSettings();

        if ($this->isOnline()) {
            $this->status = trans('trans/aside.server_status.status_on');
        } else {
            $this->status = trans('trans/aside.server_status.status_off');
        }

        $this->connected_number = MultiServerInfo::allConnected()->count();
        $this->max_connected_number = UserCount::getMaxConnectedNumber();

        if ($settings->exp_rate) {
            $this->exp_rate = $settings->exp_rate;
        }

        if ($settings->drop_rate) {
            $this->drop_rate = $settings->drop_rate;
        }

        if ($settings->penyas_rate) {
            $this->penyas_rate = $settings->penyas_rate;
        }

        $this->mvp_info = CombatInfo::getLastOnePlayed()->joinPlayer->player->m_szName ?? '-';

        // TODO: implment this info
        $this->accounts_number = 5;
        $this->players_number = 5;
        $this->gs_info = 'Neobrinoke';
        $this->lord_info = 'Neobrinoke';
        $this->event_info = 'Neobrinoke';
    }

    /**
     * Determine if server if online.
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        return @fsockopen(env('SERVER_IP'), env('SERVER_PORT'), $errno, $errstr, 0);
    }
}