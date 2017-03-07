var UE = UE || {};

var Validator = (function() {

    var Validator = function () {

        this.registerEvents = function() {
            $('#name').on("keyup", function(){
                if($(this).val() == ""){
                    $('#NameError').show();
                } else {
                    $('#NameError').hide();
                }
                this.disableSubmit();
            });

            $('#code').on("keyup", function(){
                if($(this).val() == "") {
                    $('#CodeError').show();
                } else {
                    $('#CodeError').hide();
                }
                this.disableSubmit();
            });
        };

        this.disableSubmit = function () {
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

UE.Validator = new Validator();

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