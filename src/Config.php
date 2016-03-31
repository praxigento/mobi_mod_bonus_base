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
    const CALC_STATE_COMPLETE = 'complete';
    const CALC_STATE_STARTED = 'started';
    const CODE_TYPE_ASSET_PV = PvCfg::CODE_TYPE_ASSET_PV;
    const CODE_TYPE_ASSET_WALLET_ACTIVE = WalletCfg::CODE_TYPE_ASSET_WALLET_ACTIVE;
    const MODULE = 'Praxigento_BonusBase';
}