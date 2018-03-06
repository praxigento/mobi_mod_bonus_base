<?php
/**
 * Module's configuration (hard-coded).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase;

use Praxigento\Pv\Config as PvCfg;
use Praxigento\Wallet\Config as WalletCfg;

class Config extends \Praxigento\Core\Config
{
    const ACL_BONUS_ADMIN = 'admin_bonus';
    const ACL_BONUS_CALC = self::ACL_BONUS_ADMIN;
    const ACL_BONUS_PERIOD = self::ACL_BONUS_ADMIN;
    const ACL_BONUS_RANK = self::ACL_BONUS_ADMIN;
    const ACL_BONUS_TYPE_CALC = self::ACL_BONUS_ADMIN;

    const CALC_STATE_COMPLETE = 'complete';
    const CALC_STATE_STARTED = 'started';

    const CODE_TYPE_ASSET_PV = PvCfg::CODE_TYPE_ASSET_PV;
    const CODE_TYPE_ASSET_WALLET = WalletCfg::CODE_TYPE_ASSET_WALLET;
    const DEF_MAX_DATESTAMP = '29991231';
    const MENU_BONUS_CALC = 'bonus_calc';
    const MENU_BONUS_PERIOD = 'bonus_period';
    const MENU_BONUS_RANK = 'bonus_rank';
    const MENU_BONUS_TYPE_CALC = 'bonus_type_calc';
    const MODULE = 'Praxigento_BonusBase';
}