/// <reference path="../../types/jquery.loadtemplate.d.ts"/>
import * as $ from 'jquery';
import 'jquery.loadtemplate';
import {Util} from "./Util";

/**
 * This class will load a template and fill the variables
 * with the specified data
 * 
 * @docs&source https://github.com/codepb/jquery-template
 */
export class Template{

    /**
     * Default options
     */
    private options:loadTemplateOptions = { append: true };

    /**
     *
     * @param {JQuery<HTMLElement>} $container
     * @param {string} templateId
     */
    public constructor( 
        private $container:JQuery<HTMLElement>, 
        private templateId:string
    ){
        
    }

    /**
     * Clears the container
     * @param {Object} data
     */
    public load( data:Object ){

        this.options.append = false;

        this.$container.loadTemplate( this.templateId , data, this.options );

    }

    /**
     * Appends to the container
     * @param {Object} data
     */
    public append( data:Object ){

        this.options.append = true;

        console.log(data);
        this.$container.loadTemplate( this.templateId, data, this.options );
    }

    public static convertFormDataArrayToTemplateData( formData:any, prefix:string ){

        let templateData:any = {};

        $.each( formData , ( key:string , value:string ) => {

            let keysString:string = Util.replaceAll(key, ']','').replace(prefix + '[' , '');
            let keys:string[]  = keysString.split('[');

            switch( keys.length ){
                case 1:
                    templateData[keys[0]] = Util.parseIfJson( value );
                    break;
                case 2:
                    if( typeof templateData[keys[0]] == 'undefined' ){
                        templateData[keys[0]] = {};
                    }
                    if( typeof templateData[keys[0]][keys[1]] == 'undefined' ){
                        templateData[keys[0]][keys[1]] = Util.parseIfJson( value );
                    }

                    break;
                case 3:
                    if( typeof templateData[keys[0]] == 'undefined' ){
                        templateData[keys[0]] = {};
                    }
                    if( typeof templateData[keys[0]][keys[1]] == 'undefined' ){
                        templateData[keys[0]][keys[1]] = {};
                    }
                    if( typeof templateData[keys[0]][keys[1]][keys[2]] == 'undefined' ){
                        templateData[keys[0]][keys[1]][keys[2]] = Util.parseIfJson( value );
                    }
                    break;
            }

        } );

        return templateData;
    }


}