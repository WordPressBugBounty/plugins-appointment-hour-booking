function cpappbk_renderForm(id) {
    var $iframe = jQuery('iframe[name="editor-canvas"]');
    var inIframe = $iframe.length > 0;
    
    // Search the iframe if we are in the editor, otherwise search the main document
    var $context = inIframe ? $iframe.contents() : jQuery(document);

    var $formStructure = jQuery("#form_structure" + id, $context);
    var $fbuilderDiv   = jQuery("#fbuilder_" + id, $context);

    if ($formStructure.length && $fbuilderDiv.length) {      
        
        var $tempContainer;
        var $structurePlaceholder;
        var $builderPlaceholder;

        if (inIframe) {
            // 1. Create invisible placeholders so we know exactly where to put the elements back
            $structurePlaceholder = jQuery('<div style="display:none;" id="placeholder_struct_' + id + '"></div>');
            $builderPlaceholder   = jQuery('<div style="display:none;" id="placeholder_build_' + id + '"></div>');
            
            $formStructure.before($structurePlaceholder);
            $fbuilderDiv.before($builderPlaceholder);

            // 2. Create a fake <form> in the main document. 
            // We use fixed positioning to ensure it has physical dimensions on-screen for the calendar calculations.
            $tempContainer = jQuery('<form id="temp_form_' + id + '" style="position:fixed; top:0; left:0; width:800px; height:800px; visibility:hidden; z-index:-9999;"></form>').appendTo('body');
            
            // Move elements to the main document
            $tempContainer.append($formStructure).append($fbuilderDiv);
        }

        try {
            // 3. Initialize the form builder
            var cp_appbooking_fbuilder_myconfig = {"obj":"{\"pub\":true,\"identifier\":\"_"+id+"\",\"messages\": {}}"};
            var f = jQuery("#fbuilder_" + id).fbuilder(jQuery.parseJSON(cp_appbooking_fbuilder_myconfig.obj));
            f.fBuild.loadData("form_structure" + id);
        } catch(e) {
            console.error("AHB Builder Error:", e);
        }

        if (inIframe) {
            // 4. Wait half a second to allow all asynchronous calendar math/rendering to finish
            setTimeout(function() {
                // Move elements back to their exact original locations inside the iframe
                $structurePlaceholder.before($formStructure).remove();
                $builderPlaceholder.before($fbuilderDiv).remove();
                
                // Clean up our temporary form
                $tempContainer.remove();
            }, 500); 
        }

    } else {
        // If the ServerSideRender hasn't fetched the PHP HTML yet, wait and try again
        setTimeout(function() { 
            cpappbk_renderForm(id); 
        }, 100);
    }
}

jQuery(function() {             
    (function( blocks, element, blockEditor, components, serverSideRender ) {
        var el = element.createElement;
        var Fragment = element.Fragment;
        var useEffect = element.useEffect;
        var InspectorControls = blockEditor.InspectorControls;        
        var SelectControl = components.SelectControl;
        var PanelBody = components.PanelBody;
        var ServerSideRender = serverSideRender;

        blocks.registerBlockType( 'cpapphourbk/form-rendering', {
            apiVersion: 3, 
            title: 'Appointment Hour Booking', 
            icon: 'calendar-alt',    
            category: 'cpapphourbk',
            supports: {
                customClassName: false,
                className: false
            },
            attributes: {
                formId: { type: 'string' },
                instanceId: { type: 'string' }
            },           
            edit: function( props ) {             
                var attributes = props.attributes;
                var setAttributes = props.setAttributes;
                var isSelected = props.isSelected;
                var formOptions = typeof apphourbk_forms !== 'undefined' ? apphourbk_forms.forms : [];

                useEffect(function() {
                    if (!formOptions.length) return;
                    
                    var currentFormId = attributes.formId;
                    var currentInstanceId = attributes.instanceId;
                    var needsUpdate = false;

                    if (!currentInstanceId) {                        
                        currentInstanceId = formOptions[0].value + parseInt(Math.random() * 100000, 10);
                        needsUpdate = true;
                    }
                    if (!currentFormId) {
                        currentFormId = formOptions[0].value;
                        needsUpdate = true;
                    }

                    if (needsUpdate) {
                        setAttributes({ formId: currentFormId, instanceId: currentInstanceId });
                    }

                    if (currentInstanceId) {
                        cpappbk_renderForm(currentInstanceId);
                    }
                }, []); 

                if (!formOptions.length) {
                    return el("div", null, 'Please create a booking form first.');
                }
                                                       
                return el(
                    Fragment, 
                    null,
                    isSelected && el(
                        InspectorControls,
                        { key: 'cpapphourbk_inspector' },
                        el(
                            PanelBody,
                            { title: 'Help & Support' },
                            el('span', { style: { fontStyle: 'italic' } }, 'If you need help: '),
                            el('a', { href: 'https://apphourbooking.dwbooster.com/contact-us', target: '_blank' }, 'CLICK HERE')
                        )
                    ),			    		
                    el(SelectControl, {
                        value: attributes.formId,
                        options: formOptions,
                        onChange: function(evt) {         
                            var newInstanceId = evt + parseInt(Math.random() * 100000, 10);
                            setAttributes({ formId: evt, instanceId: newInstanceId });
                            cpappbk_renderForm(newInstanceId);                                   
                        }
                    }),
                    el(ServerSideRender, {
                        block: "cpapphourbk/form-rendering",
                        attributes: attributes
                    })			    		
                );
            },
            save: function() {
                return null; 
            }
        });
    })(
        window.wp.blocks,
        window.wp.element,
        window.wp.blockEditor,
        window.wp.components,
        window.wp.serverSideRender
    );
});