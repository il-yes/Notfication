<?php

namespace Tests\NotificationBundle\Manager;


Interface ManagerInterface
{
    public function save();

    public function delete();

    public function find();

    public function getAll();
}