/*
 * Validate_signup.js
 * Validation for the signup partial
 */

var errors = 0;

function validate(){	
	if (errors == 0){
            $('#submitButton').attr('disabled', false);
        } else {
            $('#submitButton').attr('disabled', true);
        }
	}

$(function(){
	var allInputs = $('#signupForm').find('input');
	$.each(allInputs, function(key, value){
		//Initialize all inputs to false
		value.error = false;
		
		//Initialize all of the error boxes to not display by default.
		var errorBox = $("#" + value.name + "_error");
		errorBox.hide();
		
		//add a keyup funciton to all input boxes that are required for input:
		if (value.required == true){
		
			//if the value is required, make it have an error off the bat so the register button is blurred.
			value.error = true;
			errors++;
		
			$(value).on("keyup", function(){
				//get the error box for the object.
				var errorBox = $("#" + value.name + "_error");
				
				//check if this is the confirm password box.
				if (this.name == "password_confirmation")
				{
					if ($(this).val() != $('#registerPassword').val()){
						errorBox.show();
					
						//if this item previously threw an error, don't add a global error.
						if (this.error == false) {		
							this.error = true;
							errors++;
						}
					} else {	
						//if this item was previously throwing an error, remove it.
						if (this.error == true) {
							this.error = false;
							errors--;
						}
						errorBox.hide();
					}
					validate();
					return true;
				} else {
					
					//If the field is empty when there is a keychange, indicate there is an error.
					if($(this).val() == "") {
						errorBox.show();
						
						//if this item previously threw an error, don't add a global error.
						if (this.error == false) {		
							this.error = true;
							errors++;
						}
					} else {	
						//if this item was previously throwing an error, remove it.
						if (this.error == true) {
							this.error = false;
							errors--;
						}
					errorBox.hide();
					}
				validate();
			}
			});
		}
	});
	validate();
});