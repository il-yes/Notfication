<?php

namespace NotificationBundle\Manager;


Interface ManagerInterface
{
    public function save();

    public function processing($key, $value);

    public function delete($key);

}