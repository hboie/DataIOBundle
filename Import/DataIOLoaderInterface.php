<?php
/**
 * Created by PhpStorm.
 * User: SR3
 * Date: 07.11.2021
 * Time: 21:48
 */

namespace Hboie\DataIOBundle\Import;


interface DataIOLoaderInterface
{
    public function getHighestRow();

    public function getHighestColumn();

    public function getColNames();

    public function isValidColName($colName);

    public function valid();

    public function next();

    public function getCurrentRowIndex();

    public function getRow();
}