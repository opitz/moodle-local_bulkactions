define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events'], function($, str, ModalFactory, ModalEvents) {
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            var executor = function(courseid, sections, command, returnurl) {
                var params = '';

                // Check all sections
                if (command == 'check_all') {
                    $('input[class="section"]:not(:checked)').each(function() {
                        $(this).click();
                    });
                } else

                // Uncheck all sections
                if (command == 'uncheck_all') {
                    $('input[class="section"]:checked').each(function() {
                        $(this).click();
                    });
                } else
                {
                    // Check for tab moves and extract the tab nr
                    if (command.indexOf('move2tab') > -1) {
                        params = command.substr(command.length -1);
                        command = 'move2tab';
                    }
                    // Nnow execute the command with the selected sections
                    console.log('courseID = ' + courseid);
                    console.log('command = ' + command);
                    $.ajax({
                        url: "execute.php",
                        type: "POST",
                        data: {'courseid': courseid, 'command': command, 'params': params, 'sections': sections},
                        success: function(result) {
                            if(result !== '') {
                                console.log('Execution result:\n' + result);
                            } else {
                                console.log('Execution failed!\n');
                            }
                            window.location = returnurl;
                        },
                        error: function(e) {
                                console.log(e);
                        }
                    });
                }

            };
// ---------------------------------------------------------------------------------------------------------------------
            var execute = function() {
                $(".dropdown-item").on('click', function() {

                    // Get the course ID
                    var courseid = $('#courseid').val();
                    var returnurl = $('#returnurl').val();
                    var command = $(this).attr('value');
                    var confirm = $(this).attr('confirm_txt');
                    var nosectioncheck = $(this).attr('no_section_check'); // If set do not check for selected sections
                    console.log('course ID = ' + courseid);

                    // Get the selected sections
                    var sections = [];
                    $('input[class="section"]:checked').each(function() {
                        sections.push($(this).val());
                        console.log($(this).val());
                    });
                    if (sections.length === 0 && nosectioncheck !== '1') {
                        ModalFactory.create({
                            //type: ModalFactory.types.SAVE_CANCEL,
                            type: ModalFactory.types.CANCEL,
                            title: 'No Topic selected',
                            body: 'Please select at least one topic for this action.',
                        })
                            .then(function(modal) {
//                                modal.setSaveButtonText('OK');
                                modal.show();
                            });
                    } else {
                        sections = JSON.stringify(sections);
                        console.log(sections);

                        // Get the selected command
                        if(confirm === '' || confirm === false) { // No confirmation needed - do it now!
                            executor(courseid, sections, command, returnurl);
                        } else {
                            var trigger = $('.dropdown-item');
                            ModalFactory.create({
                                title: 'Please confirm',
                                body: '<p>'+confirm+'</p>',
                                type: ModalFactory.types.SAVE_CANCEL,
                            }, trigger)
                                .done(function(modal) {
                                    console.log(modal);
                                    modal.getRoot().on(ModalEvents.save, function(e) {
                                        // When modal "Save" button has been pressed.
                                        e.preventDefault();
                                        executor(courseid, sections, command, returnurl);
                                    });
                                });
                        }
                    }
                });

                $("#btn_cancel").on('click', function() {
                    window.location = $('#returnurl').val();
                });

            };

// ---------------------------------------------------------------------------------------------------------------------
            var checkAll = function() {
                $("#btn_checkall").on('click', function() {
                    $('input[class="section"]:not(:checked)').each(function() {
                        $(this).click();
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var uncheckAll = function() {
                $("#btn_uncheckall").on('click', function() {
                    $('input[class="section"]:checked').each(function() {
                        $(this).click();
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Load all required functions above
                execute();
                checkAll();
                uncheckAll();
//                $('.dropdown-toggle').dropdown();
            };

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                console.log('=================< execute.js >==================');
                initFunctions();

            });
        }
    };
});