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
		//Initialize all input errors to false
		value.error = false;
		
		//Initialize all of the error boxes to not display by default.
		var errorBox = $("#" + value.name + "_error");
		errorBox.hide();
		
		//add a keyup funciton to all input boxes that are required for input:
		if (value.required == true){
			$(value).on("keyup", function(){
				//get the error box for the object.
				var errorBox = $("#" + value.name + "_error");
				
				//check if this is the confirm password box.
				if (this.name == "password_confirmation" || this.name == "password")
				{
					//if the user is trying to change their password,
					if ($(accountPassword) != ""){
						//if the passwords don't match,
						if ($('#accountPasswordConfirm').val() != $('#accountPassword').val()){
							//show the error.
							$('#password_confirmation_error').show();
						
							//if the passwords didn't previously throw an error make it throw an error
							if ($('#accountPassword')[0].error == false) {		
								$('#accountPassword')[0].error = true;
								errors++;
							}
						} else {	
							//if this item was previously throwing an error, remove it.
							if ($('#accountPassword')[0].error == true) {
								$('#accountPassword')[0].error = false;
								errors--;
							}
							$('#password_confirmation_error').hide();
						}
						validate();
						return;
					}
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