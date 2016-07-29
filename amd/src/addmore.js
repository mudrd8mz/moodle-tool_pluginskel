define(['jquery'], function($) {

    return {
        addMore: function(templatevars) {

            $("[name*='addmore_']").on('click', function() {
                var variableName = $(this).prop('name').replace('addmore_', '');
                var variableCount = +$("[name='"+variableName+"count']").val();
                var templateVariable;

                for (var i in templatevars) {
                    if (templatevars[i].name == variableName) {
                        templateVariable = templatevars[i];
                        break;
                    }
                }

                var newElementName;
                var newElementId;
                var fieldVariable;

                var prevIndex = variableCount-1;
                var prevElementName;
                var prevElementId;
                var prevElementClasses;
                var prevElementHtml;

                var newElements = '<br/>';
                for (i in templateVariable.values) {
                    fieldVariable = templateVariable.values[i];
                    newElementName = variableName+'['+variableCount+']['+fieldVariable.name+']';
                    newElementId = 'id_'+variableName+'_'+variableCount+'_'+fieldVariable.name;

                    prevElementName = variableName+'['+prevIndex+']['+fieldVariable.name+']';
                    prevElementId = 'id_'+variableName+'_'+prevIndex+'_'+fieldVariable.name;
                    prevElementClasses = $('#fitem_'+prevElementId).attr('class');
                    prevElementHtml = $('#fitem_'+prevElementId).html();

                    // Replace all occurrences of previous name.
                    // Using a RegExp class with replace doesn't seem to be working.
                    while (prevElementHtml.indexOf(prevElementName) != -1) {
                        prevElementHtml = prevElementHtml.replace(prevElementName, newElementName);
                    }

                    // Replace all occurrences of previous id.
                    while (prevElementHtml.indexOf(prevElementId) != -1) {
                        prevElementHtml = prevElementHtml.replace(prevElementId, newElementId);
                    }

                    // Add the new elements inside a div similar to the original.
                    newElements += '<div id="fitem_'+newElementId+'" class="'+prevElementClasses+'">';
                    newElements += prevElementHtml;
                    newElements += '</div>';
                }

                $('#fitem_id_addmore_'+variableName).before(newElements);

                // Removing original DOM node input or choices.
                for (i in templateVariable.values) {
                    fieldVariable = templateVariable.values[i];
                    newElementId = 'id_'+variableName+'_'+variableCount+'_'+fieldVariable.name;

                    if (!('hint' in fieldVariable) || fieldVariable.hint == 'text' || fieldVariable.hint == 'int') {
                        $('#'+newElementId).removeAttr('value');
                        continue;
                    }

                    if (fieldVariable.hint == 'multiple-options') {
                        for (i in fieldVariable.values) {
                            if (i == 'none') {
                                $('#'+newElementId).val('none').change();
                            }
                        }
                    }
                }

                // Increment the number of variable elements.
                $("[name='"+variableName+"count']").val(variableCount + 1);
            });
        }
    };
});
