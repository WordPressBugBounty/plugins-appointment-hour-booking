<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


global $wpdb;

$this->setId (isset($_GET["ahbcalendar"]) ? intval($_GET["ahbcalendar"]) : 0);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('manage_options');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))))
{
    echo 'Direct access not allowed.';
    exit;
}

if ( !is_admin() )
{
    echo 'Direct access not allowed.';
    exit;
}

$myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items );
if (!$this->getId() && count ($myrows)) $this->setId($myrows[0]->id);

// 07\edit_style.css
$css = "";
$raw_form_str = str_replace("\r"," ",str_replace("\n"," ",$this->cleanJSON($this->translate_json($this->get_option('form_structure', CP_APPBOOK_DEFAULT_form_structure)))));
$form_data = json_decode( $raw_form_str );
if( is_null( $form_data ) ){
    $json = new JSON;
    $form_data = $json->unserialize( $raw_form_str );
}
$form_template = '';
if( !is_null( $form_data ) ) {
    if( isset( $form_data[ 1 ] ) && isset( $form_data[ 1 ][ 0 ] ) && isset( $form_data[ 1 ][ 0 ]->formtemplate ) )
    {
        $templatelist = $this->available_templates();
        $form_template = substr(md5($form_data[ 1 ][ 0 ]->formtemplate),0,10);
        if(  isset( $templatelist[ $form_data[ 1 ][ 0 ]->formtemplate ] ) )
            $file = $templatelist[ $form_data[ 1 ][ 0 ]->formtemplate ][ 'file' ];
        else 
            $file =  plugins_url( 'templates/edit_style.css', __FILE__ );
        $path = parse_url($file, PHP_URL_PATH);
        $desiredPart = substr($path, strpos($path, 'templates/'));
        $tpls_dir =  dir( plugin_dir_path( __FILE__ ) );          
        $ecssname = dirname($tpls_dir->path.$desiredPart)."/edit_style.css";
        if (file_exists($ecssname))
            $css = file_get_contents($ecssname);
    }      
}

if ( isset( $_POST['csseditionon'] )) 
{
    $this->verify_nonce (sanitize_text_field($_POST["anonce"]), 'cpappb_actions_csseditor');
    if (!empty($_POST["savecss"])) 
    {
        update_option('AHB_CUSTOM_CSS_'.$this->getId().$form_template, sanitize_text_field($_POST["customcsscontents"]));
        $message = __('Styles saved','appointment-hour-booking'); 
    } 
    else if (!empty($_POST["resetcss"])) 
    {
        update_option('AHB_CUSTOM_CSS_'.$this->getId().$form_template, '');
        $message = __('Styles restored to original','appointment-hour-booking'); 
    }
      
}

$nonce = wp_create_nonce( 'cpappb_actions_csseditor' );

    
?>
<style>

    .button-primary2 { background-color: #AADDFF !important; }
    .button-primary3 { background-color: #CCFFFF !important; }
    .ahb-section-container {
        border: 1px solid #e6e6e6;
        padding: 20px;
        border-radius: 3px;
        -webkit-box-flex: 1;
        flex: 1;
        margin: 1em 1em 1em 0;
        min-width: 200px;
        background: white;
        position:relative;
    }

	.clear{clear:both;}
	.ahb-first-button{margin-right:10px !important;}
    .ahb-buttons-container{margin:1em 1em 1em 0;}
    .ahb-return-link{float:right;}
    .ahbadminoptions {display: none}
    
    #myModal {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            max-height: 500px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 10000000; 
            overflow: auto;
        }
        #modalBackground {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999999;
        }
        .close-button {
            color: #aaa;
            font-size: 14px;
            font-weight: bold;
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .divbuttonCode {
            position: relative;
            border-top: 1px solid #ccc;
            margin-top: 10px;
            color:#aaa;
            background-color:#eee;
        }
        .buttonCode {
            position: absolute;
            right: 0px;
            top: 0px;
            background: #ccc;
            padding: 2px 5px;
            cursor:pointer;
            color:#666;
        }
        h3{font-size:16px}
        .cp_csseditor{border:1px dashed #2271b1!important}
        .cp_csseditor .cp_csseditor{border:2px dashed #2271b1!important}
        .cp_csseditor .cp_csseditor .cp_csseditor{border:3px dashed #2271b1!important}
</style>

<h1><?php esc_html_e('Styles Editor','appointment-hour-booking'); ?> <?php if ($this->item) echo ' - '. esc_html($this->get_option("form_name")); ?></h1>



<div class="ahb-section-container" style="margin-bottom:20px;style:clear:both;">   

	<div class="ahb-section">
	  
      <form action="admin.php" method="get">
        <input type="hidden" name="page" value="<?php echo $this->menu_parameter; ?>_csseditor_page" />           
        <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />        
        <div style="float:left">
            <select id="ahbcalendar" name="ahbcalendar">        
           <?php
            $saved_id = $this->getId();
            foreach ($myrows as $item)
            {        
                $this->setId($item->id);
                if ($current_user_access || @in_array($current_user->ID, unserialize($this->get_option("cp_user_access",""))))
                   echo '<option value="'.$item->id.'"'.(intval($item->id)==intval($saved_id)?" selected":"").'>'.esc_html($item->form_name).'</option>';
            }    
            $this->setId($saved_id);
           ?>
            </select>          
		    <nobr>     
			<input type="submit" name="<?php echo $this->prefix; ?>_load" value="<?php esc_html_e('Load Form','appointment-hour-booking'); ?>" class="button" style="margin-left:10px;float:right">			
		    </nobr>
       </div>
       <br >       
      </form>
      
      <div style="clear:both;margin-bottom:15px;"></div>           
      
      <form action="" name="saverestoreform" method="post" onsubmit="return confirmoption();">
        <input type="hidden" name="page" value="<?php echo $this->menu_parameter; ?>_csseditor_page" />  
        <input type="hidden" name="csseditionon" value="1" />          
        <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />        
        <div style="float:left">
            <input type="hidden" name="customcsscontents" id="customcsscontents" value="<?php echo esc_attr(get_option('AHB_CUSTOM_CSS_'.$this->getId().$form_template,"")); ?>">        

            <input type="submit" name="resetcss" class="button-primary3 button" value="<?php esc_html_e('Restore Original Styles','appointment-hour-booking'); ?>" style="margin-left:10px;float:right">	
            
			<input type="submit" class="button-primary button" name="savecss" value="<?php esc_html_e('Save Edited Styles','appointment-hour-booking'); ?>"  style="float:right">			
		    
       </div>
       <br >       
      </form>
      
        <?php if (!empty($message)) echo "<div style=\"clear:both;margin-bottom:15px;\"></div><div id='setting-error-settings_updated' class='updated'><h2>".esc_html($message)."</h2></div>";     ?>
              


        <div id="ahbcustomizationarea" style="padding:10px;margin-right:10px;clear:both;background-color: white">  
        <hr />
        <div id="modalBackground"></div>
                    <div id="myModal">
                        <button class="close-button">Close</button>
                        <div id="editor"></div>	
                    </div>
        <?php $this->output_filter_content(array('id' => $this->item)); ?>

        <em><?php esc_html_e('* Remember to click the "Save Edited Styles" button to save the modified styles.','appointment-hour-booking'); ?></em>

        </div> 

	</div>
    
</div>    

<script>

function confirmoption() {
    var form = document.saverestoreform;
    if (form.resetcss && form.resetcss === document.activeElement) {
        return confirm('<?php esc_js(esc_html_e('Are you sure you want to restore original styles?','appointment-hour-booking')); ?>');
    }
    return true;
}

jQuery(document).on("showHideDepEvent", function(){
    (function($) {   
        function clickFirstSlot(){
		    if ($(".availableslot a").length>0)
		    {
		        $(".cp_csseditor").removeClass("cp_csseditor");
		        $(".availableslot a").click();
		    }    
		    else
		        setTimeout(clickFirstSlot,100); 
		           
		}
		clickFirstSlot(); 
        $.datepicker._attachHandlers = function() {
            return false;
        };
            
                    let cssString = `
                <?php echo esc_html($css); ?>
                     `;
                if ($("#customcsscontents").val()!="")
                    cssString = $("#customcsscontents").val();
                const rulesArray = parseCssString(cssString);
                generateModifiedCss(rulesArray);
                updateRulesArray(rulesArray);
	            
                $(document).on('input',".colorValue", function() {
                    $(this).parent().find('.colorPicker').val($(this).val());
                    updateRulesArray(rulesArray);
                    generateModifiedCss(rulesArray);               
                });
                $(document).on('input',".colorPicker", function() {
                    $(this).parent().find('.colorValue').val($(this).val());
                    updateRulesArray(rulesArray);
                    generateModifiedCss(rulesArray); 
                });
                $(document).on('input',".inputText", function() { 
                    updateRulesArray(rulesArray);
                    generateModifiedCss(rulesArray); 
                });
                $('#rangeInput').on('input', function() {
                    var value = $(this).val(); // Obtener el valor del input de rango
                    $('#textInput').val(value); // Actualizar el input de texto
                });
                
                // Actualizar el input de rango cuando se cambia el input de texto
                $('#textInput').on('input', function() {
                    var value = $(this).val(); // Obtener el valor del input de texto
                    $('#rangeInput').val(value); // Actualizar el input de rango
                });
			    for (let i=0;i<rulesArray.length;i++) {
			        $(document).on('mouseover',rulesArray[i].selector, function() {
			            $(".cp_csseditor").each(function(){
			                if ($(this).find(rulesArray[i].selector).length == 0)
			                    $(this).removeClass(".cp_csseditor");     
			            });
                        $(rulesArray[i].selector).addClass("cp_csseditor");
                    });
                    $(document).on('mouseout',rulesArray[i].selector, function() {                        
                        if (!$('#myModal').is(':visible'))
                            $(rulesArray[i].selector).removeClass("cp_csseditor");
                    });
                    $(document).off('click', "#fieldlist_1 .pbSubmit");
                    
                    $(document).on('click',rulesArray[i].selector, function(event) {
                        event.stopImmediatePropagation();
                        event.stopPropagation();
			    	    $("#myModal").attr('class', '').addClass(genName(rulesArray[i].selector,"_m"));
			    		generateCssEditor(rulesArray,i);
                        $('#modalBackground, #myModal'+"."+genName(rulesArray[i].selector,"_m")).fadeIn();
                        event.stopImmediatePropagation();
                        return false;
                    });
                    
                    $(document).click(function(event) {
                        if (!$(event.target).closest('#myModal, '+rulesArray[i].selector).length) {
                            //$('#modalBackground, #myModal'+"."+genName(rulesArray[i].selector,"_m")).fadeOut();
                        }
                    });   
			    }
            
        
        function genName(s,p) {
            return s.replace(/[():.#\s]/g, '_')+p; // Reemplaza ., # y espacios con _
        }
        function parseCssString(cssString) {
            const rulesArray = [];
            const mergeCss = [{s:"border",e:"width"},{s:"border",e:"color"},{s:"border",e:"style"}];
            const styleElement = document.createElement('style');
            styleElement.appendChild(document.createTextNode(cssString));
            document.head.appendChild(styleElement);
        
            Array.from(styleElement.sheet.cssRules).forEach(rule => {
                if (rule.selectorText) {
                    const ruleInfo = {
                        selector: rule.selectorText.replace(':hover',""),
                        pseudoClass:"",
                        style: {}
                    };
                    Array.from(rule.style).forEach(property => {
                        const computedValue = rule.style.getPropertyValue(property).trim();
                        // Renombrar 'background-color' a 'background' solo si no está en cssText
                        if (property === 'background-color' && !rule.cssText.includes('background-color')) {
                            property = 'background';
                        }
                        for (var i=0;i<mergeCss.length;i++)
                            if (rule.cssText.includes(mergeCss[i].s+"-"+mergeCss[i].e) && property.startsWith(mergeCss[i].s+"-") && property.includes("-"+mergeCss[i].e))
                                property = mergeCss[i].s+"-"+mergeCss[i].e;
                        // Convertir RGB a HEX si la propiedad es un color
                        if (computedValue.startsWith('rgb')) {
                            ruleInfo.style[property] = rgbToHex(computedValue);
                        } else if (computedValue !== 'initial') {
                            ruleInfo.style[property] = computedValue;
                        }
                    });   
                    
                    //// Incluir pseudo-clases en el objeto
                    if (rule.selectorText.includes(':hover')) {
                        rule.selectorText = rule.selectorText.replace(':hover',"")
                        ruleInfo.pseudoClass = ':hover';
                    }
                    if (Object.keys(ruleInfo.style).length > 0) {
                        rulesArray.push(ruleInfo);
                    }
                }
            });
        
            styleElement.remove();
            return rulesArray;
        }
        
        // Función para convertir RGB a HEX
        function rgbToHex(rgb) {
            const result = rgb.match(/\d+/g); // Extraer los valores numéricos
            if (!result) return rgb; // Si no hay coincidencias, devolver el valor original
        
            return `#${((1 << 24) + (result[0] << 16) + (result[1] << 8) + +result[2]).toString(16).slice(1)}`; // Convertir a HEX
        }
        
        function generateCssEditor(parsedCssRules,visible) {
            
            const $editor = $('#editor');
            $editor.empty();
            // Diccionario de propiedades CSS y sus valores permitidos
            const cssPropertyValues = {
                
                'Select property': [],
                'background': ['none', 'red', 'blue', 'green', 'yellow', 'orange', 'black', 'white', '#ffffff'],
                'background-color': ['none', 'red', 'blue', 'green', 'yellow', 'orange', 'black', 'white', '#ffffff'],
                'color': ['red', 'blue', 'green', 'yellow', 'orange', 'black', 'white', '#ffffff'],
                'font-size': ['12px', '14px', '16px', '18px', '20px', '24px'],
                'font-weight': ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500'],
                'font-family': ['auto', 'cursive', 'emoji', 'none', 'sans-serif', 'serif', 'ui-monospace'],
                'line-height': ['normal','12px', '14px', '16px', '18px', '20px', '24px'],
                'display': ['block', 'inline', 'inline-block', 'flex', 'grid', 'none'],
                'text-align': ['left', 'right', 'center', 'justify'],
                'border': ['1px solid black', '2px dashed red', '3px dotted blue'],
                'margin': ['0', '5px', '10px', '20px'],
                'padding': ['0', '5px', '10px', '20px'],
                'width': ['auto', '100%', '50%', '200px'],
                'height': ['auto', '100%', '50%', '200px'],
                'z-index': ['0', '1', '2', '100'],                
                'text-shadow': ['none', 'red', 'blue', 'green', 'yellow', 'orange', 'black', 'white'],
                'box-shadow': ['none', 'red', 'blue', 'green', 'yellow', 'orange', 'black', 'white'],
                'border-radius': ['inherit','2px', '4px', '6px', '10%', '20%', '50%'],
                'opacity': ['0', '0.5', '1'],
            };
            parsedCssRules.forEach((rule, index) => {
                const $ruleContainer = $('<div>').addClass('rule-container').attr('data-index', index);
                if (visible!=index)
                    $ruleContainer.hide() 
                if (rule.pseudoClass!="" && visible==index-1)
                    $ruleContainer.show()     
                $ruleContainer.append(`<h3>${rule.selector}${rule.pseudoClass}</h3>`);
                
                // Crear una tabla para las propiedades y valores
                const $table = $('<table cellpadding="5" cellspacing="0" width="100%">');
        
                // Iterar sobre las propiedades de estilo
                Object.entries(rule.style).forEach(([property, value]) => {
                    const $tr = $('<tr>'); // Crear una fila para cada propiedad
                    let $label = $('<td >').text(property); // Celda para la propiedad
                    let $input,$input1,$inputCell; 
                    if (property.includes('-color') || property === 'color' || property === 'background') {
                        $input = $('<input class="colorPicker">').attr({
                            type: 'color',
                            value: value || '#ffffff'
                        });
                        $input1 = $('<input class="colorValue">').attr({
                            type: 'text',
                            value: value || '#ffffff'
                        });                        
                        $inputCell = $('<td nowrap>').append($input).append($input1);
                    } else if (property.includes('--comment')) {
                        $label = $('<td >').html("<b>*</b>Note");                       
                        $inputCell = $('<td nowrap>').append(value.replace( /-/g, "<b>" ));
                    } else {
                        $input = $('<input  class="inputText">').attr({
                            type: 'text',
                            value: value
                        });
                        $inputCell = $('<td style="white-space: nowrap;">').append($input);
                    }
                    let $delProperty = $('<td >').append($('<button class="delProperty">Del</button>')); // Celda para borrarla
        
                    $tr.append($label).append($inputCell).append($delProperty); // Añadir celdas a la fila
                    $table.append($tr); // Añadir fila a la tabla
                });
                 // Añadir botón para agregar nuevas propiedades
                const $addPropertyRow = $('<tr style="background-color:#ddd">');
                const $addPropertyLabel = $('<td>').append('<label for="propertySelect">New Property:</label>');
                const $propertySelect = $('<select class="newProperty">');
                
                // Poblar el select con propiedades CSS
                Object.keys(cssPropertyValues).forEach(prop => {
                    $propertySelect.append(`<option value="${prop}">${prop}</option>`);
                });
                
                const $addPropertyValue = $('<select class="newValue"></select>'); // Cambiado a select
                const $addButton = $('<button class="addProperty">Add</button>');
                $addPropertyRow.append($addPropertyLabel).append($('<td>').append($propertySelect).append($addPropertyValue)).append($('<td>').append($addButton));
                $table.append($addPropertyRow);
                
                $propertySelect.on('change', function() {
                    const selectedProperty = $(this).val();
                    // Si la propiedad seleccionada es de color o background, mostrar un input de tipo color
                    if (selectedProperty.includes('background') || selectedProperty.includes('color')) {
                        const $newColorInput = $('<input class="newValue">').attr({
                            type: 'color',
                            value: '#ffffff'  // Valor por defecto
                        });
                        $(this).parent().find(".newValue").replaceWith($newColorInput);
                    
                    } else {
                        const $newSelect = $('<select class="newValue"></select>');
                        cssPropertyValues[selectedProperty].forEach(value => {
                            $newSelect.append(`<option value="${value}">${value}</option>`);
                        });
                        $(this).parent().find(".newValue").replaceWith($newSelect);
                    
                    }
                }); 
                //$propertySelect.change();
                // Acción del botón "Agregar Propiedad"
                $addButton.on('click', function() {
                    const propertyName = $addPropertyRow.find('.newProperty').val();
                    const propertyValue = $addPropertyRow.find('.newValue').val();
                    if (propertyName && propertyValue) { 
                        let index = $(this).parents(".rule-container").attr('data-index');                       
                        parsedCssRules[index].style[propertyName] = propertyValue; // Agregar la nueva propiedad al objeto de regla
                        if (parsedCssRules[index].pseudoClass == ':hover') index--; // Es para el caso especial del hover que muestra las dos reglas
                        generateCssEditor(parsedCssRules, index); // Volver a renderizar el editor para mostrar la nueva propiedad                        
                        generateModifiedCss(parsedCssRules);
                    } else {
                        alert('Please provide both property name and value.');
                    }
                });            
                $ruleContainer.append($table); // Añadir tabla al contenedor de la regla
                $styleElement = $('<pre class="preCode" style="margin-top:20px;overflow:auto">');
                const selector = rule.selector.replace(':hover',"");
                const pseudoClass = rule.pseudoClass;
                const styles = Object.entries(rule.style)
                    .filter(([property, value]) => !property.includes('--comment'))
                    .map(([property, value]) => `${property}: ${value};`)
                    .join(' ');
        
                $styleElement.append(`${selector}${pseudoClass} { ${styles} }\n`);
                $copyB = $('<div class="buttonCode">').html("Copy CSS rule");
                $ruleContainer.append($("<div class='divbuttonCode'>").append($styleElement).append($copyB)); 
                //$ruleContainer.append('<button class="buttonCode">Copy CSS rule</button>')
                $editor.append($ruleContainer); // Añadir contenedor de regla al editor
            });
        }
        
        function updateRulesArray(rulesArray) {
            $('.rule-container').each(function() {
                const $container = $(this);
                const index = $container.data('index');
                const selector = $container.find('h3').text();
                const updatedStyle = {};
            
                $container.find('input').each(function() {
                    const property = $(this).parents('tr').find('td').first().text();
                    const value = $(this).val();
                    if (property != "--comment")
                        updatedStyle[property] = value;
                });
            
                rulesArray[index] = {
                    selector: selector.replace(':hover',""),
                    pseudoClass: rulesArray[index].pseudoClass,
                    style: updatedStyle
                };
            });
        }  
        function generateModifiedCss(rulesArray) {
            let $styleElement = $('#dynamicStyles');
        
            if ($styleElement.length === 0) {
                $styleElement = $('<style>', { id: 'dynamicStyles' });
                $('body').append($styleElement);
            }
            $styleElement.empty();
            let $c = "";
            rulesArray.forEach((rule, index) => {
                const selector = rule.selector.replace(':hover',"")+rule.pseudoClass;
                let styles = Object.entries(rule.style)
                    .filter(([property, value]) => !property.includes('--comment'))
                    .map(([property, value]) => `${property}: ${value};`)
                    .join(' ');
                $c += `${selector} { ${styles} }\n`;
                
                $('.rule-container[data-index="'+index+'"] pre').text(`${selector} { ${styles} }\n`);
            });
            $styleElement.append($c);
            $("#customcsscontents").val($c)
        }
       
            
            $(document).on('click',".buttonCode", function(event) {
                const preText = $('.preCode:visible').text();
                const $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(preText).select();
                document.execCommand('copy');
                $temp.remove();
                alert('CSS rule copied to clipboard!');
            });
            $(document).on('click',".delProperty", function(event) { 
                    const propertyName = $(this).parents("tr").find("td").first().text();
                    let index = $(this).parents(".rule-container").attr('data-index');
                    let r = rulesArray[index];
                    if (r.style.hasOwnProperty(propertyName)) {
                        delete r.style[propertyName]; // Eliminar la propiedad
                        if (r.pseudoClass == ':hover') index--; // Es para el caso especial del hover que muestra las dos reglas
                        generateCssEditor(rulesArray, index); // Volver a renderizar el editor para mostrar la nueva propiedad                        
                        generateModifiedCss(rulesArray);
                    }
                    
                });  
            $(document).on('click',".close-button", function(event) {
                $('#modalBackground, #myModal').fadeOut();
            });
            
        })(jQuery);
});         
</script> 
  
  
  <hr />
  
<div class="ahb-section-container">
	<div class="ahb-section">  
    
    <h2><?php esc_html_e('Remember that you can select pre-defined style templates in the following area:','appointment-hour-booking'); ?></h2>
    <a href="<?php echo esc_attr(plugins_url('/images/selecting-templates.png', __FILE__)); ?>" target="_blank"><img src="<?php echo esc_attr(plugins_url('/images/selecting-templates.png', __FILE__)); ?>" width="400px" style="border:1px dotted black;padding:10px;background-color:#ffffff"></a>
  </div>  
</div>  
  