<?php if(!class_exists('raintpl')){exit;}?><html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="tpl/js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="tpl/js/additional-methods.min.js"></script>
        <script type="text/javascript" src="tpl/js/jquery.form.js"></script>
        <link rel="stylesheet" type="text/css" href="tpl/stylesheets/main.css" />
        <script>
            $(document).ready(function() {
                $("body").css("display", "none");
                
                $("body").fadeIn(1000);
                
                $("a.transition").click(function(event){
                    event.preventDefault();
                    linkLocation = this.href;
                    $("body").fadeOut(1000, redirectPage);
                });
                
                function redirectPage() {
                    window.location = linkLocation;
                }
                
                jQuery.validator.addMethod("mac", function(value, element) { 
                    regex=/^([0-9a-f]{2}([:-])){5}([0-9a-f]{2})$|^([0-9a-f]{2}([.])){5}([0-9a-f]{2})$|^([0-9a-f]{12})$/i;
                    if (regex.test(value)){
                            return true;
                    }
                    else{
                            return false;
                    }
                }, "Please specify a valid mac address");
                
                jQuery("#add").validate();
                jQuery('#add').ajaxForm(function(responseText, statusText, xhr, $form) { 
                    jQuery('#add').resetForm();
                    if(statusText == 'success') {
                        var response = jQuery.parseJSON(responseText);
                        $('#message').html(response.status);
                        $('#status').append('Added Blah to Blah </br>');
                        $('#mac').focus();
                    }
                });
            });
        </script>
    </head>
        <body>