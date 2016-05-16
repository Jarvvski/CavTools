/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    // new namespace for this add-on
    XenForo.CavTools =
    {
        Issue: function($form)
        {
            // bind a function onto the AutoValidationComplete event of the form AutoValidator
            $form.bind('AutoValidationComplete', function(e)
            {

                // prevent the normal AutoValidator success message and redirect stuff
                e.preventDefault();

                // clear the textarea contents and refocus it
                $form.find('textarea[name=title]').val('').focus();
                $form.find('textarea[name=problem]').val('');
                $form.find('textarea[name=reason]').val('');
            });
        }
    };

    // register the functionality
    XenForo.register('form.submitIssue', 'XenForo.CavTools.Issue');

}
(jQuery, this, document);