<?php

class CavTools_listener_LoadClassController
{
  public static function loadClassListener($class, &$extend)
  {
    if ($class == 'XenForo_ControllerPublic_Account')
    {
      $extend[] = 'CavTools_ControllerPublic_MilpacsLinker';
    }
  }
}
