define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events'], function($, str, ModalFactory, ModalEvents) {
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            var executor = function(courseid, sections, command, returnurl) {
                var params = '';

                // check all sections
                if(command == 'check_all') {
                    $('input[class="section"]:not(:checked)').each(function() {
                        $(this).click();
                    });
                } else

                // uncheck all sections
                if(command == 'uncheck_all') {
                    $('input[class="section"]:checked').each(function() {
                        $(this).click();
                    });
                } else
                {
                    // check for tab moves and extract the tab nr
                    if (command.indexOf('move2tab') > -1) {
                        params = command.substr(command.length -1);
                        command = 'move2tab';
                    }
                    // now execute the command with the selected sections
                    $.ajax({
                        url: "execute.php",
                        type: "POST",
                        data: {'courseid': courseid, 'command': command, 'params': params, 'sections': sections},
                        success: function(result) {
                            if(result !== '') {
                                console.log('Execution result:\n' + result);
                                window.location = returnurl;
                            }
                        }
                    });
                }

            };
// ---------------------------------------------------------------------------------------------------------------------
            var execute = function() {
                $(".dropdown-item").on('click', function() {

                    // get the course ID
                    var courseid = $('#courseid').val();
                    var returnurl = $('#returnurl').val();
                    var confirm = $(this).attr('confirm_txt');
                    console.log('course ID = ' + courseid);

                    // get the selected sections
                    var sections = [];
                    $('input[class="section"]:checked').each(function() {
                        sections.push($(this).val());
                        console.log($(this).val());
                    });
                    sections = JSON.stringify(sections);
                    console.log(sections);

                    // get the selected command
                    var command = $(this).attr('value');
                    if(confirm === '') { // no confirmation needed - do it now!
                        executor(courseid, sections, command, returnurl);
                    } else {
                        var trigger = $('.dropdown-item');
                        ModalFactory.create({
                            title: 'Please confirm',
//                            body: '<p>Do you really want to do this?</p>',
                            body: '<p>'+confirm+'</p>',
                            type: ModalFactory.types.SAVE_CANCEL,
                        }, trigger)
                            .done(function(modal) {
                                modal.getRoot().on(ModalEvents.save, function(e) {
                                    // When modal "Save" button has been pressed.
                                    e.preventDefault();
                                    executor(courseid, sections, command, returnurl);
                                });
                            });
                    }
                });

                $("#btn_cancel").on('click', function() {
                    window.location = $('#returnurl').val();
                });

            };
            var execute0 = function() {
                $(".dropdown-item").on('click', function() {

                    // get the course ID
                    var courseid = $('#courseid').val();
                    var returnurl = $('#returnurl').val();
                    console.log('course ID = ' + courseid);

                    // get the selected command
//                    var command = $('#command option:selected').val();
                    var command = $(this).attr('value');
                    if(command === '') {
                        $('#message').html('Please select an Action');
                    } else {
                        // get the selected sections
                        var sections = [];
                        $('input[class="section"]:checked').each(function() {
                            sections.push($(this).val());
                            console.log($(this).val());
                        });
                        sections = JSON.stringify(sections);
                        console.log(sections);

                        var params = '';

                        // check for tab moves and extract the tab nr
                        if (command.indexOf('move2tab') > -1) {
                            params = command.substr(command.length -1);
                            command = 'move2tab';
                        }
                        // now execute the command with the selected sections
                        $.ajax({
                            url: "execute.php",
                            type: "POST",
                            data: {'courseid': courseid, 'command': command, 'params': params, 'sections': sections},
                            success: function(result) {
                                if(result !== '') {
                                    console.log('Execution result:\n' + result);
                                    window.location = returnurl;
                                }
                            }
                        });
                    }
                });

                $("#btn_cancel").on('click', function() {
                    window.location = $('#returnurl').val();
                });

            };
            var execute00 = function() {
                $("#btn_execute").on('click', function() {

                    // get the course ID
                    var courseid = $('#courseid').val();
                    var returnurl = $('#returnurl').val();
                    console.log('course ID = ' + courseid);

                    // get the selected command
                    var command = $('#command option:selected').val();
                    console.log('---------');
                    console.log(command);
                    console.log('---------');

                    if(command === '') {
                        $('#message').html('Please select an Action');
                    } else {
                        // get the selected sections
                        var sections = [];
                        $('input[class="section"]:checked').each(function() {
                            sections.push($(this).val());
                            console.log($(this).val());
                        });
                        sections = JSON.stringify(sections);
                        console.log(sections);

                        var params = '';

                        // check for tab moves and extract the tab nr
                        if (command.indexOf('move2tab') > -1) {
                            params = command.substr(command.length -1);
                            command = 'move2tab';
                        }
                        // now execute the command with the selected sections
                        $.ajax({
                            url: "execute.php",
                            type: "POST",
                            data: {'courseid': courseid, 'command': command, 'params': params, 'sections': sections},
                            success: function(result) {
                                if(result !== '') {
                                    console.log('Execution result:\n' + result);
                                    window.location = returnurl;
                                }
                            }
                        });
                    }
                });

                $("#btn_cancel").on('click', function() {
                    window.location = $('#returnurl').val();
                });

            };

// ---------------------------------------------------------------------------------------------------------------------
            var checkAll = function() {
                $("#btn_checkall").on('click', function() {
//                    $('input[class="section"]').attr('checked', 'checked').removeAttr('id').
//                    parent().removeAttr('id').parent().removeAttr('id');
                    $('input[class="section"]:not(:checked)').each(function() {
                        $(this).click();
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var uncheckAll = function() {
                $("#btn_uncheckall").on('click', function() {
//                    $('input[class="section"]').removeAttr('checked').removeAttr('id').
//                    parent().removeAttr('id').parent().removeAttr('id');
                    $('input[class="section"]:checked').each(function() {
                        $(this).click();
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var droptest = function() {
                $(".dropdown-item0").on('click', function() {
                    alert($(this).attr('value'));
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var modaltest = function() {
                $("#btn_test").on('click', function() {
//                    alert('test button clicked');
                    var trigger = $('#btn_test');
                    ModalFactory.create({
                        title: 'test title',
                        body: '<p>test body content</p>',
                        type: ModalFactory.types.SAVE_CANCEL,
                    }, trigger)
                        .done(function(modal) {
                            // Do what you want with your new modal.
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                // When modal "Save" button has been pressed.
                                e.preventDefault();
                                console.log('modal saving here..');
                            });
                        });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Load all required functions above
                modaltest();
                execute();
                checkAll();
                uncheckAll();
                $('.dropdown-toggle').dropdown();
            };

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                console.log('=================< execute.js >==================');
                initFunctions();

            });
        }
    };
});