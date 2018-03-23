<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode;

use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ECalcType;
use Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder as QBBase;

/**
 * Build query to get periods with related calculations data by DATE, CALC_TYPE_CODE & CALC_STATE.
 *
 * TODO: should we extends \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder here ?
 */
class Builder
    extends \Praxigento\Core\App\Repo\Query\Def\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_CALC = QBBase::AS_CALC;
    const AS_CALC_TYPE = QBBase::AS_CALC_TYPE;
    const AS_PERIOD = QBBase::AS_PERIOD;

    /** Columns/expressions aliases for external usage ('underscore' naming for database fields; 'camelCase' naming for aliases) */
    const A_CALC_ID = QBBase::A_CALC_ID;
    const A_CALC_STATE = QBBase::A_CALC_STATE;
    const A_CALC_TYPE_CODE = QBBase::A_CALC_TYPE_CODE;
    const A_CALC_TYPE_ID = QBBase::A_CALC_TYPE_ID;
    const A_CALC_TYPE_NOTE = QBBase::A_CALC_TYPE_NOTE;
    const A_DATE_ENDED = QBBase::A_DATE_ENDED;
    const A_DATE_STARTED = QBBase::A_DATE_STARTED;
    const A_DS_BEGIN = QBBase::A_DS_BEGIN;
    const A_DS_END = QBBase::A_DS_END;
    const A_PERIOD_ID = QBBase::A_PERIOD_ID;

    const BND_CODE = 'code';
    const BND_DATE = 'lastDate';
    const BND_STATE = 'state';


    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
    protected $qbldBase;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder $qbldBase
    ) {
        parent::__construct($resource);
        $this->qbldBase = $qbldBase;
    }

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = $this->qbldBase->build(); // build root query

        /* then add WHERE filters */

        /* get the last calculation by type code & state */
        $whereType = self::AS_CALC_TYPE . '.' . ECalcType::A_CODE . '=:' . self::BND_CODE;
        $whereState = self::AS_CALC . '.' . ECalc::A_STATE . '=:' . self::BND_STATE;
        $result->where("$whereType AND $whereState");

        /* add filter for periods by MAX date */
        $whereDate = self::AS_PERIOD . '.' . EPeriod::A_DSTAMP_END . '<=:' . self::BND_DATE;
        $result->where($whereDate);

        /*
            sort desc and limit results if there are more than one calculations with the same code&state
            for the period
        */
        $result->order(self::AS_PERIOD . '.' . EPeriod::A_DSTAMP_END . ' DESC');
        $result->limit(1);


        return $result;
    }

}