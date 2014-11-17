<?php

namespace SenseiApply\Controllers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Iterator\ItemIterator;

class ResumesController
{
    /** @var DynamoDbClient */
    private $dynamoDb;
    
    /** @var string */
    private $tableName;
    
    /** @var \Twig_Environment */
    private $twig;

    /**
     * @param DynamoDbClient $dynamoDb
     */
    public function setDynamoDb($dynamoDb)
    {
        $this->dynamoDb = $dynamoDb;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }

    public function index()
    {
        return $this->twig->render('resumes/index.twig', [
            'itemList' => new ItemIterator($this->dynamoDb->getIterator('Scan', [
                'TableName' => $this->tableName
            ]))
        ]);
    }
}
