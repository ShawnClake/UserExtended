var errors = 0;

function validate(){
	if (errors != 0){
            $('#submitButton').attr('disabled', true);
        } else {
            $('#submitButton').attr('disabled', false);
        }
	}

$('#registeremail').on("keyup", function(){
	if($(this).val() == "") {
		$('#emailError').show();
	} else {
		$('#emailError').hide();
	}
	validate();
});

$('#userSigninPassword').on("keyup", function(){
	if($(this).val() == "") {
		$('#passwordError').show();
	} else {
		$('#passwordError').hide();
	}
	validate();
});

$(function(){
	var allInputs = $('#signupForm').find('input');
	$.each(allInputs, function(key, value){
		//Initialize all inputs to have errors
		value.error = true;
		errors++;
		
		//Initialize all of the error boxes and not display by default.
		var errorBox = $("#" + value.name + "_error");
		errorBox.hide();
		
		$(value).on("keyup", function(){
		var errorBox = $("#" + value.name + "_error");
		
			if($(this).val() == "") {
				if (this.error == false){
					this.error = true;
					errors++;
				}
				errorBox.show();
			} else {
				if (this.error == true){
					this.error = false;
					errors--;
				}
				errorBox.hide();
			}
		validate();
		});
	});
	validate();
});