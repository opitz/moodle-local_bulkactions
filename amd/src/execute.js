define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events'], function($, str, ModalFactory, ModalEvents) {
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            // Moving section0 to the ontop area
            var execute = function() {
                $("#btn_execute").on('click', function() {

                    // get the course ID
                    var courseid = $('#courseid').val();
                    console.log('course ID = ' + courseid);

                    // get the selected command
                    var command = $('#command option:selected').val();

                    // get the selected sections
                    var sections = [];
                    $('input[class="section"]:checked').each(function() {
                        sections.push($(this).val());
                        console.log($(this).val());
                    });
                    sections = JSON.stringify(sections);
                    console.log(sections);

                    // now execute the command with the selected sections
                    $.ajax({
                        url: "commands/" + command,
                        type: "POST",
                        data: {'courseid': courseid, 'sections': sections},
                        success: function(result) {
                            if(result !== '') {
                                console.log('Execution result:\n' + result);
                            }
                        }
                    });


                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Load all required functions above
                execute();
            };

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                console.log('=================< execute.js >==================');
                initFunctions();

            });
        }
    };
});