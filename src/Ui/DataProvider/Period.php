<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Ui\DataProvider;

/**
 * Data provider for "Bonus / Periods" grid.
 */
class Period
    extends \Praxigento\Core\Ui\DataProvider\Base
{

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Praxigento\Core\Repo\Query\Criteria\IAdapter $criteriaAdapter,
        \Praxigento\BonusBase\Repo\Entity\IPeriod $repo,
        \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        $name,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $url,
            $criteriaAdapter,
            null,
            $repo,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $name,
            $meta,
            $data
        );
    }

}