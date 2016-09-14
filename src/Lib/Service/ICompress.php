<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Lib\Service;

use Praxigento\BonusBase\Lib\Service\Compress\Request\QualifyByUserData as QualifyByUserDataRequest;
use Praxigento\BonusBase\Lib\Service\Compress\Response\QualifyByUserData as QualifyByUserDataResponse;

interface ICompress {

    /**
     * @param QualifyByUserDataRequest $request
     *
     * @return QualifyByUserDataResponse
     */
    public function qualifyByUserData(QualifyByUserDataRequest $request);
}