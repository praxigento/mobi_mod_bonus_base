<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis;

/**
 * @method string getAssetTypeCode()
 * @method string getCalcCode()
 * @method string getPeriodType()
 * @method void setAssetTypeCode(string $data)
 * @method void setCalcCode(string $data)
 * @method void setPeriodType(string $data)
 */
class Request
    extends \Praxigento\Core\App\Service\Request
{
    const ASSET_TYPE_CODE = 'assetTypeCode';
    const CALC_CODE = 'calcCode';
    const PERIOD_TYPE = 'periodType';
}
