<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Def;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Module_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
{
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mDba;
    /** @var  \Mockery\MockInterface */
    private $mRepoGeneric;
    /** @var  \Mockery\MockInterface */
    private $mToolDate;
    /** @var  Module */
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped('Test is deprecated after M1 & M2 merge is done.');
        $this->mConn = $this->_mockDba();
        $this->mDba = $this->_mockResourceConnection($this->mConn);
        $this->mRepoGeneric = $this->_mockRepoGeneric($this->mDba);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $this->repo = new Module(
            $this->mRepoGeneric,
            $this->mToolDate
        );
    }


    public function test_getCalcsForPeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $DS_BEGIN = '20160304';
        $DS_END = '20160308';
        $AS_ID = 'id';
        $CALC_ID = 23;
        $SHOULD_GET_LATEST = true;
        $CALCS_FOUND = [[$AS_ID => $CALC_ID]];

        /** === Setup Mocks === */
        // $tblPeriod = $this->_getTableName(Period::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $conn->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from([ $asPeriod => $tblPeriod ], [ ]);
        $mQuery->shouldReceive('from');
        // $query->join([ $asCalc => $tblCalc ], $on, $cols);
        $mQuery->shouldReceive('joinLeft');
        // $query->limit(1);
        $mQuery->shouldReceive('limit')->with(1);
        // $conn->quote(...);
        $this->mConn->shouldReceive('quote');
        // $query->where("$whereTypeId AND $whereFrom AND $whereTo");
        $mQuery->shouldReceive('where');
        // $result = $conn->fetchAll($query);
        $this->mConn
            ->shouldReceive('fetchAll')
            ->andReturn($CALCS_FOUND);

        /** === Call and asserts  === */
        $resp = $this->repo->getCalcsForPeriod($CALC_TYPE_ID, $DS_BEGIN, $DS_END, $SHOULD_GET_LATEST);
        $this->assertTrue(is_array($resp));
        $this->assertEquals($CALC_ID, $resp[$AS_ID]);
    }


}