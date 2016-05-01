<?php

class CavTools_Listener_LoadClassController
{
  public static function loadClassListener($class, &$extend)
  {
    if ($class == 'XenForo_ControllerPublic_Member')
    {
      $extend[] = 'CavTools_ControllerPublic_MilpacsLinker';
    }
  }
}
