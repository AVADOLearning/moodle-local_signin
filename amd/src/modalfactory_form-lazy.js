define([
    'jquery',
    'core/modal_factory'
], function($, ModalFactory) {

    return {
        init: function(options) {
            function handleUserSubmit(e) {
                e.preventDefault();
                $(".modal").modal().toggle();
            }

            ModalFactory.create({
                body: options.body
            }, $(options.triggerSignin))
                .done(function(modal) {
                    modal.body.delegate($(options.triggerUsernameSubmit), 'click', handleUserSubmit);
                });
        }
    };

});
