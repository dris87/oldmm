/// <reference path="../../types/all4one/jquery.autocomplete.d.ts"/>
import * as $ from 'jquery';
import '../../../../../../src/All4One/AutocompleteBundle/Resources/public/autocomplete.js';

export default class Collection{

    public constructor(
        private $collectionElement:JQuery<HTMLElement>,
        private autocompleteSelector:string = '.select2-autocomplete-field',
        private addSelector:string = '.collection-add',
        private deleteSelector:string = '.collection-delete'
    ){

        let self = this;

        this.$collectionElement.each(function () {
            let widget:JQuery<HTMLElement> = $(this);

            widget.on('click',addSelector, () => {

                let container:JQuery<HTMLElement> = widget.find('.collection');
                let index:number = container.children().length;

                let contentHtml = container
                    .attr('data-prototype')
                    .replace(/\_\_name\_\_/g , index.toString());

                let content:JQuery<HTMLElement> = $(contentHtml);

                container.append(content);

                content.find(autocompleteSelector).all4oneAutocomplete();

                return false;
            });

            widget.on('keypress','input[type=text]',function(e) {
                // Create a new row and focus on that on enter action ;)
                if(e.which == 13 ) {
                    if( $(this).val().toString().length > 0 ) {
                        widget.find(self.addSelector).trigger('click');
                        widget.find('input[type=text]:last-child').focus();
                    }
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            widget.on('keyup','input[type=text]',function(e) {
                // Remove the current row if backspace is pressed on an empty input
                if( e.which == 8 && $(this).val().toString().length == 0 && widget.find('input[type=text]').length > 1 ){

                    $(this).parents('.collection-row').remove();
                    widget.find('input[type=text]:last-child').focus();
                    e.preventDefault();
                    e.stopPropagation();

                }
            });
            widget.on('click',deleteSelector, function () {
                let row:JQuery<HTMLElement> = $(this).parents('.collection-row:first');
                let container:JQuery<HTMLElement> = $(this).parents('.collection:first');
                row.remove();
                container.children().each(function (index:number) {

                    $(this).find(':input').each(function () {
                        let name:string = $(this).attr('name');

                        if( typeof name === 'undefined' ){
                            return;
                        }

                        name = name.replace(/\d+/, index.toString() );
                        $(this).attr('name', name);
                    });

                });

                return false;
            });
        });

    }

}
