<?php
class CavTools_Listeners_OptionGroupTabs
{
    /***
    * This is the event listener callback. All the params are explanaded in the Code Event Listener page in the AdminCP.
    */
    public static function templatePostRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        /* If the template is the one we want to change */
        if ($templateName == 'option_list')
        {
            /* If we are viewing our addon options page */
            if ($containerData['title'] == 'CavTools')
            {
                /* Change the default options list template to our new one */
                $content = $template->create('OptionsAsTabs', $template->getParams());
            }
        }
    }
}
