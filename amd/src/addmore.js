define(['jquery'], function($) {

    var addElements = {

        /**
         * Deletes a an array template variable value.
         *
         * @param {Array} templateVars The template variables.
         * @param {String} variableName The name of the variable.
         */
        deleteElements: function(templateVars, variableName, elt) {

            var variableCount = +$("[name='"+variableName+"count']").val();

            if (variableCount == 1) {
                return;
            }

            var templateVariable;

            for (var i in templateVars) {
                if (templateVars[i].name == variableName) {
                    templateVariable = templateVars[i];
                    break;
                }
            }

            var lastIndex = variableCount - 1;

            // Removing the newline before the element that will be deleted.
            $('#id_'+variableName+' br').filter(':last').remove();

            for (i in templateVariable.values) {
                var fieldVariable = templateVariable.values[i];
                var lastElementDiv = 'fitem_id_'+variableName+'_'+lastIndex+'_'+fieldVariable.name;

                $('#'+lastElementDiv).remove();

                if (fieldVariable.hint === 'array') {

                    var nestedCountVar = variableName+'_'+lastIndex+'_'+fieldVariable.name+'count';
                    var nestedCount = +$("[name='"+nestedCountVar+"']").val();

                    // Removing the newlines between the nested array elements.
                    $('#id_'+variableName+' br').slice(-(nestedCount - 1)).remove();

                    for (var j in fieldVariable.values) {

                        var nestedVariable = fieldVariable.values[j];

                        for (var k = 0; k < nestedCount; k++) {
                            var nestedDiv = lastElementDiv+'_'+k+'_'+nestedVariable.name;
                            $('#'+nestedDiv).remove();
                        }
                    }

                    // Removing the 'Add more ..' button.
                    var buttonDivId = 'fitem_id_addmore_'+variableName+'_'+lastIndex+'_'+fieldVariable.name;
                    $('#'+buttonDivId).remove();

                    // Remove the number of nested variables
                    $("[name='"+nestedCountVar+"']").remove();
                }
            }

            // Decrement the number of variable elements.
            $("[name='"+variableName+"count']").val(variableCount - 1);
        },

        /**
         * Adds more elements to a fieldset located at the root of the document.
         *
         * @param {Array} templateVars The template variables.
         * @param {String} variableName The name of the variable.
         */
        addMoreRootElements: function(templateVars, variableName, elt) {
            var variableCount = +$("[name='"+variableName+"count']").val();
            var templateVariable;

            for (var i in templateVars) {
                if (templateVars[i].name == variableName) {
                    templateVariable = templateVars[i];
                    break;
                }
            }

            var prevIndex = variableCount-1;
            var newElements = '<br/>';

            var elementIdPrefix;
            var newButtonId;
            var newButtonName;
            var hasNestedFields = false;

            for (i in templateVariable.values) {
                var fieldVariable = templateVariable.values[i];

                var newElementName = variableName+'['+variableCount+']['+fieldVariable.name+']';
                var newElementId = 'id_'+variableName+'_'+variableCount+'_'+fieldVariable.name;

                var prevElementName = variableName+'['+prevIndex+']['+fieldVariable.name+']';
                var prevElementId = 'id_'+variableName+'_'+prevIndex+'_'+fieldVariable.name;

                if (fieldVariable.hint == 'array') {

                    hasNestedFields = true;

                    // Cloning the static label.
                    var prevElementClasses = $('#fitem_'+prevElementId).attr('class');
                    newElements += '<div id="fitem_'+newElementId+'" class="'+prevElementClasses+'">';
                    newElements += $('#fitem_'+prevElementId).html();
                    newElements += '</div>';

                    for (var j in fieldVariable.values) {
                        var nestedVariable = fieldVariable.values[j];

                        var newNestedElementName = newElementName+'[0]['+nestedVariable.name+']';
                        var newNestedElementId = newElementId+'_0_'+nestedVariable.name;

                        var prevNestedElementName = prevElementName+'[0]['+nestedVariable.name+']';
                        var prevNestedElementId = prevElementId+'_0_'+nestedVariable.name;

                        newElements += addElements.cloneElement(prevNestedElementName, prevNestedElementId,
                                                                newNestedElementName, newNestedElementId);
                    }

                    // Cloning the 'Add more ..' button.
                    newButtonId = 'id_addmore_'+variableName+'_'+variableCount+'_'+fieldVariable.name;
                    newButtonName = 'addmore_'+variableName+'['+variableCount+']['+fieldVariable.name+']';

                    var prevButtonId = 'id_addmore_'+variableName+'_'+prevIndex+'_'+fieldVariable.name;
                    var prevButtonName = 'addmore_'+variableName+'['+prevIndex+']['+fieldVariable.name+']';

                    newElements += addElements.cloneElement(prevButtonName, prevButtonId,
                                                            newButtonName, newButtonId);

                    // Adding the hidden count field.
                    var prevCountName = variableName+'_'+prevIndex+'_'+fieldVariable.name+'count';
                    var newCountName = variableName+'_'+variableCount+'_'+fieldVariable.name+'count';

                    var newCountHtml = '<input name="'+newCountName+'" value="1" type="hidden"></input>';
                    $('[name="'+prevCountName+'"]').after(newCountHtml);

                } else {
                    newElements += addElements.cloneElement(prevElementName, prevElementId,
                                                            newElementName, newElementId);
                }
            }

            $('#fgroup_id_buttons_'+variableName).before(newElements);

            elementIdPrefix = 'id_'+variableName+'_'+variableCount;
            addElements.removeInputFromNewNode(templateVariable, elementIdPrefix);

            // Increment the number of variable elements.
            $("[name='"+variableName+"count']").val(variableCount + 1);

            // Attaching the event to the newly added button.
            if (hasNestedFields === true) {
                return [[newButtonId, newButtonName]];
            } else {
                return [];
            }
        },

        /**
         * Adds more elements nested inside a fieldset element.
         *
         * @param {Array} templateVars The template variables.
         * @param {String} variableName The name of the variable.
         */
        addMoreNestedElements: function(templateVars, variableName, elt) {

            var topVariableName = variableName.substr(0, variableName.indexOf('['));
            variableName = variableName.substr(variableName.indexOf('[') + 1);
            var topIndex = variableName.substr(0, variableName.indexOf(']'));
            variableName = variableName.substr(variableName.indexOf(']') + 2);
            var nestedVariableName = variableName.substr(0, variableName.indexOf(']'));

            var namePrefix = topVariableName+'['+topIndex+']['+nestedVariableName+']';
            var idPrefix = 'id_'+topVariableName+'_'+topIndex+'_'+nestedVariableName;

            var variableCountName = topVariableName+'_'+topIndex+'_'+nestedVariableName+'count';
            var variableCount = +$("[name='"+variableCountName+"']").val();

            var templateVariable;
            for (var i in templateVars) {
                if (templateVars[i].name == topVariableName) {
                    for (var j in templateVars[i].values) {
                        if (templateVars[i].values[j].name == nestedVariableName) {
                            templateVariable = templateVars[i].values[j];
                            break;
                        }
                    }
                }
            }

            var prevIndex = variableCount-1;
            var newElements = '<br/>';

            for (i in templateVariable.values) {

                var fieldVariable = templateVariable.values[i];

                var newElementName = namePrefix+'['+variableCount+']['+fieldVariable.name+']';
                var newElementId = idPrefix+'_'+variableCount+'_'+fieldVariable.name;

                var prevElementName = namePrefix+'['+prevIndex+']['+fieldVariable.name+']';
                var prevElementId = idPrefix+'_'+prevIndex+'_'+fieldVariable.name;

                newElements += addElements.cloneElement(prevElementName, prevElementId,
                                                        newElementName, newElementId);
            }

            $('#fitem_id_addmore_'+topVariableName+'_'+topIndex+'_'+nestedVariableName).before(newElements);

            var elementIdPrefix = idPrefix+'_'+variableCount;
            addElements.removeInputFromNewNode(templateVariable, elementIdPrefix);

            // Increment the number of variable elements.
            $("[name='"+variableCountName+"']").val(variableCount + 1);

        },

        /**
         * Removes previous input from the newly added nodes.
         *
         * @param {Array} templateVariable The variable from which the added elements were created.
         * @param {String} elementIdPrefix The prefix for the new elements' id.
         */
        removeInputFromNewNode: function(templateVariable, elementIdPrefix) {

            for (var i in templateVariable.values) {

                var fieldVariable = templateVariable.values[i];
                var newElementId = elementIdPrefix+'_'+fieldVariable.name;

                if (fieldVariable.hint == 'text' || fieldVariable.hint == 'int') {
                    $('#'+newElementId).removeAttr('value');
                }

                if (fieldVariable.hint == 'multiple-options') {
                    var isRequired = ('required' in fieldVariable) && fieldVariable.required === true;
                    if (!isRequired) {
                        $('#'+newElementId+' option[value="none"]').prop('selected', true).change();
                    }
                }

                if (fieldVariable.hint == 'array') {
                    var newIdPrefix = newElementId+'_0';
                    addElements.removeInputFromNewNode(fieldVariable, newIdPrefix);
                }
            }
        },

        /**
         * Creates a clone of a previous element.
         *
         * @param {String} prevElementName
         * @param {String} prevElementId
         * @param {String} newElementName
         * @param {String} newElementId
         */
        cloneElement: function(prevElementName, prevElementId, newElementName, newElementId) {
            var prevElementClasses = $('#fitem_'+prevElementId).attr('class');
            var newElementHtml = $('#fitem_'+prevElementId).html();

            while (newElementHtml.indexOf(prevElementId) != -1) {
                newElementHtml = newElementHtml.replace(prevElementId, newElementId);
            }

            while (newElementHtml.indexOf(prevElementName) != -1) {
                newElementHtml = newElementHtml.replace(prevElementName, newElementName);
            }

            var newElement = '<div id="fitem_'+newElementId+'" class="'+prevElementClasses+'">';
            newElement += newElementHtml;
            newElement += '</div>';

            return newElement;
        },

        /**
         * Adds more fields for an array template variable when the 'Add more .. ' button is clicked.
         *
         * @param {Array} templateVars The template variables.
         */
        addMore: function(templateVars) {

            $("[name*='addmore_']").on('click', function() {

                var variableName = $(this).prop('name').replace('addmore_', '');

                if (variableName.indexOf('[') == -1) {
                    var nestedButtons = addElements.addMoreRootElements(templateVars, variableName, this);

                    // Attaching the on click event to the newly added buttons.
                    if (nestedButtons.length > 0) {
                        for (var i in nestedButtons) {
                            var newId = nestedButtons[i][0];
                            var newName = nestedButtons[i][1];
                            $('#'+newId).on('click', function() {
                                addElements.addMoreNestedElements(templateVars,
                                                                  newName.replace('addmore_', ''),
                                                                  this);
                            });
                        }
                    }
                } else {
                    addElements.addMoreNestedElements(templateVars, variableName, this);
                }
            });

            $("[name*='delete_']").on('click', function() {

                var variableName = $(this).prop('name').replace('delete_', '');
                addElements.deleteElements(templateVars, variableName, this);

            });
        },
    };

    return addElements;
});
