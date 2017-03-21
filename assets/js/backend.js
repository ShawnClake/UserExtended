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

$(window).load(function() {
    console.log('test');
    interact('.draggable-row')
        .draggable({
            // enable inertial throwing
            inertia: true,
            // keep the element within the area of it's parent

            // enable autoScroll
            autoScroll: true,
            restrict: {
                restriction: "drop_container",
                endOnly: true,
                elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
            },
            // call this function on every dragmove event
            onmove: dragMoveListener,
            onstart: coordSaver,
            onend: resetPos
            // call this function on every dragend event
        });

    function resetPos (event) {
        var target = $(event.target);
        var hasClass = target.hasClass('can-drop');
        if(!hasClass)
        {
            target.css('-webkit-transform', 'none');
            target.css('transform', 'none');
            event.target.setAttribute('data-x', 0);
            event.target.setAttribute('data-y', 0);
        }
    }

    function coordSaver (event) {
        var target = event.target;
        var x = (parseFloat(target.getAttribute('data-x')) || 0);
        var y = (parseFloat(target.getAttribute('data-y')) || 0);
        target.setAttribute('original-x', x);
        target.setAttribute('original-y', y);
    }

    function dragMoveListener (event) {
        var target = event.target,
            // keep the dragged position in the data-x/data-y attributes
            x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx,
            y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        // translate the element
        target.style.webkitTransform =
            target.style.transform =
                'translate(' + x + 'px, ' + y + 'px)';

        // update the position attributes
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    // this is used later in the resizing and gesture demos
    //window.dragMoveListener = dragMoveListener;

    function makeDropzone(dropDiv, acceptedClass, ajaxCall)
    {
        interact(dropDiv).dropzone({
            // only accept elements matching this CSS selector
            accept: acceptedClass,
            // Require a 75% element overlap for a drop to be possible
            overlap: 0.5,
            ondrop: function (event) {
                //console.log("Drop Event");
                //console.log($(event.relatedTarget).find('[data-request="onAssignRole"]').data('request-data'));
                var requestData = $(event.relatedTarget).find('[data-request="'+ajaxCall+'"]').data('request-data');
                //console.log(requestData);
                var requestDataSubArr = requestData.split(',');
                //console.log(JSON.stringify(requestDataSubArr));

                var request = {};
                for(var i = 0; i < requestDataSubArr.length; i++)
                {
                    //console.log(requestDataSubArr[i].split('\'').join(''));
                    var split = requestDataSubArr[i].split('\'').join('').split(':');
                    request[split[0].trim()] = split[1].trim();
                }

                //console.log(JSON.stringify(request));

                $.request(ajaxCall, {data: request}, {success: function(data) {
                    //... do something ...
                    this.success(data);
                }});
                //$.request('onAssignRole', {data: {roleCode: }});
                //event.relatedTarget.textContent = 'Dropped';
            },
            ondragenter: function(event) {
                event.relatedTarget.classList.add('can-drop');
                $(event.relatedTarget).css('background-color', 'rgba(165, 209, 151, 0.8)');
                $(event.relatedTarget).css('border-radius', '7px');
                $(event.relatedTarget).css('z-index', '9999');
            },
            ondragleave: function (event) {
                event.relatedTarget.classList.remove('can-drop');
                $(event.relatedTarget).css('background-color', '');
                $(event.relatedTarget).css('border-radius', '');
                $(event.relatedTarget).css('z-index', '999');
            }
        });
    }

    $(function() {
        makeDropzone('#list_roles_container', '.drag-unassigned-role', 'onAssignRole');
        makeDropzone('#list_unassigned_roles_container', '.drag-role', 'onUnassignRole');
        makeDropzone('#list_users_container', '.drag-unassigned-user', 'onAssignUser');
        makeDropzone('#list_unassigned_users_container', '.drag-user', 'onUnassignUser');
    });

});



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