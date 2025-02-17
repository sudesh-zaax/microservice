<?php

namespace App\Interfaces;

interface MenuRepositoryInterface
{
  /**
   * Summary of getRoleWiseMenu
   * @return void
   */
  public function getRoleWiseMenu():array;

  /**
   * Summary of getPageContent
   * @param int $page_id
   * @return array
   */
  public function getPageContent(int $page_id):array;

  public function syncUri($service):array;
}
