<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service;

use Praxigento\BonusBase\Service\Compress\Request\QualifyByUserData as QualifyByUserDataRequest;
use Praxigento\BonusBase\Service\Compress\Response\QualifyByUserData as QualifyByUserDataResponse;

interface ICompress {

    /**
     * @param QualifyByUserDataRequest $request
     *
     * @return QualifyByUserDataResponse
     */
    public function qualifyByUserData(QualifyByUserDataRequest $request);
}