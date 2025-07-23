/// <reference path="../../types/all4one/jquery.autocomplete.d.ts"/>
import * as $ from 'jquery';
import 'select2';
import '../../../../../../src/All4One/AutocompleteBundle/Resources/public/autocomplete.js';

/**
 * This autocomplete components is not the as
 * the jquery.autocomplete.js plugin.
 * This component is for select2 in our case
 * but here we have an adapter like stuff for
 * managing select2 like plugins in one plage :)
 */
export default class Autocomplete{

    /**
     * By defining all default options, we can make sure
     * that on version change we will not have default
     * value changes(hence unwanted behaviour)
     */
    private static options:any = {

    }

    public constructor(){

        $('.select2-autocomplete-field').all4oneAutocomplete();

    }

    static init( selector:string = '.select2', additionalOptions:any = {}){

        let options = (<any>Object).assign( Autocomplete.options , additionalOptions );

        $( selector ).select2( (<any>Object).options );
        
    }

    /**
     * Symfony birth date type gives us 3 inputs, 
     * this will make their with equal
     * 
     * @param classSelector 
     * @param additionalOptions 
     */
    public initGroups( classSelector:string = 'enable-select2', additionalOptions:any = {} ){

        let select2Groups = $('.'+ classSelector +' select');

        $.each( select2Groups , function(){
            
            let width = 100 / select2Groups.length;
    
            $( this ).select2({
                width: width + '%'
            });
    
        });
        
        $('select.' + classSelector ).select2({
            width: '100%'
        });

    }

}
