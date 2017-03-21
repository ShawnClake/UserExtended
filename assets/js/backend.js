/*
 * Backend.js
 * Javascript for UE backend pages
 */

/*var UE = UE || {};
UE.Utils = UE.Utils || {};

(function (Validator, $, undefined) {

    Validator.registerEvents = function() {
        $('#name').on("keyup", function(){
            if($(this).val() == ""){
                $('#NameError').show();
            } else {
                $('#NameError').hide();
            }
            Validator.disableSubmit();
        });

        $('#code').on("keyup", function(){
            if($(this).val() == "") {
                $('#CodeError').show();
            } else {
                $('#CodeError').hide();
            }
            Validator.disableSubmit();
        });
    };

    Validator.disableSubmit = function () {
        if($('#name').val() == "" || $('#code').val() == "")
        {
            $('#ErrorBox').show();
            $('#submitButton').attr('disabled', true);
        } else {
            $('#ErrorBox').hide();
            $('#submitButton').attr('disabled', false);
        }
    };

} (UE.Utils.Validator = UE.Utils.Validator || {}, jQuery));*/

var UE = UE || {};
UE.Utils = UE.Utils || {};

var Validator = (function() {

    var Validator = function () {

        this.registerEvents = function() {
            $('#name').on("keyup", function(){
                if($(this).val() == ""){
                    $('#NameError').show();
                } else {
                    $('#NameError').hide();
                }
                disableSubmit();
            });

            $('#code').on("keyup", function(){
                if($(this).val() == "") {
                    $('#CodeError').show();
                } else {
                    $('#CodeError').hide();
                }
                disableSubmit();
            });
			disableSubmit();
        };

        disableSubmit = function () {
            if($('#name').val() == "" || $('#code').val() == "")
            {
                $('#ErrorBox').show();
                $('#submitButton').attr('disabled', true);
            } else {
                $('#ErrorBox').hide();
                $('#submitButton').attr('disabled', false);
            }
        };

    };

    return Validator;

})();

UE.Utils.Validator = new Validator();






/*
 function disableSubmit()
 {
 if($('#name').val() == "" || $('#code').val() == "")
 {
 $('#ErrorBox').show();
 $('#submitButton').attr('disabled', true);
 } else {
 $('#ErrorBox').hide();
 $('#submitButton').attr('disabled', false);
 }
 }

 $('#name').on("keyup", function(){
 if($(this).val() == ""){
 $('#NameError').show();
 } else {
 $('#NameError').hide();
 }
 disableSubmit();
 });

 $('#code').on("keyup", function(){
 if($(this).val() == "") {
 $('#CodeError').show();
 } else {
 $('#CodeError').hide();
 }
 disableSubmit();
 });

 $(function(){
 disableSubmit();
 });
    */