/**
 * Backend.js
 * Javascript for UE backend pages
 */

$(function() {
    if(UE.Config.Debug)
    {
        console.log("User Extended BackendJS loading...");
        console.log("User Extended BackendJS loaded.");
    }
});

UE.Utils = UE.Utils || {};
UE.Interactions = UE.Interactions || {};

/**
 * Config and setup
 */
UE.Interactions.Config = {
    'DragDrop': {
        'Threshold': 0.40,
        'Inertia': true,
        'AutoScroll': true
    }
};

UE.Interactions.Dropzones = {
    'onAssignRole': {
        'drops': ['#list_roles_container'],
        'row': '.drag-unassigned-role'
    },
    'onUnassignRole': {
        'drops': ['#list_unassigned_roles_container'],
        'row': '.drag-role'
    },
    'onAssignUser': {
        'drops': ['#list_users_container'],
        'row': '.drag-unassigned-user'
    },
    'onUnassignUser': {
        'drops': ['#list_unassigned_users_container'],
        'row': '.drag-user'
    }
};

/**
 * Validator class
 */
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

/**
 * Interactions
 */
$(window).load(function() {
    console.log('test');
    interact('.draggable-row')
        .draggable({
            // enable inertial throwing
            inertia: UE.Interactions.Config.DragDrop.Inertia,
            // keep the element within the area of it's parent

            // enable autoScroll
            autoScroll: UE.Interactions.Config.DragDrop.AutoScroll,
            restrict: {
                restriction: "drop_container",
                endOnly: true,
                elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
            },
            // call this function on every dragmove event
            onmove: moveEventHandler,
            onstart: startEventHandler,
            onend: stopEventHandler
            // call this function on every dragend event
        });

    function stopEventHandler (event) {
        var target = $(event.target);
        var hasClass = target.hasClass('can-drop');
        if(!hasClass)
        {
            target.css('-webkit-transform', 'none');
            target.css('transform', 'none');
            event.target.setAttribute('data-x', 0);
            event.target.setAttribute('data-y', 0);
        }

        var request = $(target).data('handler');
        console.log($(target));
        var drops = UE.Interactions.Dropzones[request]['drops'];
        for(var i = 0; i < drops.length; i++)
        {
            var element = $(drops[i]);
            element.css('background-color', '');
            element.css('border-radius', '');
        }
    }

    function startEventHandler (event) {
        var target = event.target;
        var x = (parseFloat(target.getAttribute('data-x')) || 0);
        var y = (parseFloat(target.getAttribute('data-y')) || 0);
        target.setAttribute('original-x', x);
        target.setAttribute('original-y', y);

        var request = $(target).data('handler');
        console.log($(target));
        var drops = UE.Interactions.Dropzones[request]['drops'];
        for(var i = 0; i < drops.length; i++)
        {
            var element = $(drops[i]);
            element.css('background-color', 'rgba(165, 209, 151, 0.8)');
            element.css('border-radius', '7px');
        }
    }

    function moveEventHandler (event) {
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
            overlap: UE.Interactions.Config.DragDrop.Threshold,
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
                $(event.target).css('background-color', 'rgba(114, 181, 92, 0.8)');
                $(event.relatedTarget).css('border-radius', '7px');
                $(event.relatedTarget).css('z-index', '100');
            },
            ondragleave: function (event) {
                event.relatedTarget.classList.remove('can-drop');
                $(event.target).css('background-color', 'rgba(165, 209, 151, 0.8)');
                $(event.relatedTarget).css('border-radius', '');
                $(event.relatedTarget).css('z-index', '99');
            }
        });
    }

    $(function() {
        for(var handler in UE.Interactions.Dropzones)
        {
            if(!UE.Interactions.Dropzones.hasOwnProperty(handler)) continue;

            var drops = UE.Interactions.Dropzones[handler]['drops'];
            var row = UE.Interactions.Dropzones[handler]['row'];
            console.log(handler);
            console.log(row);
            console.log(drops);
            for(var i = 0; i < drops.length; i++)
            {
                makeDropzone(drops[i], row, handler);
            }
        }

    });

});
