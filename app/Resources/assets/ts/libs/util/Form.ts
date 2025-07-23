import * as $ from 'jquery';
import { Loader } from '../../components/atoms/Loader';
import { SystemConfig } from '../SystemConfig';
import { Framework } from '../Framework';
import { Request } from './Request';
import { Util } from './Util';

/**
 * This class will grab a form jquery object and submits it.
 * It will handle the loading animation, and the input blocking on requests
 */
export class Form{

    /**
     * In debug = true, we do not show loading
     * and console.log the data
     */
    public debug:boolean = false;

    /**
     * We use this to store the input elements, which has some error on them
     */
    private errorInputGroups:Array<JQuery<HTMLElement>> = [];

    /**
     * If true, the form will never submit
     */
    private isDisabled:boolean = false;

    /**
     * Loader instance
     */
    private loader: Loader;

    /**
     * Any default ajax options here
     * @type {{}}
     */
    private options:JQuery.AjaxSettings = {};

    /**
     * Request object instance
     */
    private request:Request;


    /**
     * Holds the last array of data, which was sent
     */
    private lastData:Array<any>;

    /**
     * Holds the last array of data, which was sent
     * note: this is the array version of it.
     */
    private lastDataArray:any = {};

    /**
     * Holds the old url
     */
    private url:string;

    /**
     *
     * @param {JQuery<HTMLElement>} $formElement
     * @param {JQuery.AjaxSettings} options
     * @param {boolean} serializeArray
     * @param {JQuery<HTMLElement>} $loaderElement
     */
    public constructor( private $formElement:JQuery<HTMLElement>, options?:JQuery.AjaxSettings , private serializeArray:boolean = false , $loaderElement:JQuery<HTMLElement> = null ){

        this.debug = SystemConfig.debug;

        this.url = this.$formElement.prop('action');

        let method = this.$formElement.prop('method');
        this.options.type = ( [ 'GET' , 'POST' , 'PUT' ].indexOf( method ) > -1 ) ? method : 'POST';
        this.options.url = ( this.url.length > 0 ) ? this.url : location.href;

        this.options = (<any>Object).assign( this.options , options );

        if( this.debug !== true ){
            this.loader = new Loader( ($loaderElement != null && $loaderElement.length > 0) ? $loaderElement : $formElement );
        }

        this.request = new Request( this.options );

        this.addCompleteEvent(()=>{});
        this.addValidationSuccessEvent(()=>{});

        this.$formElement.on('submit', ( e ) => {
            let type = this.$formElement.find("button[type=submit][clicked=true]").prop('name');
            this.submit(type);
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        this.$formElement.on('click', 'button[type=submit]', function() {
            $formElement.find('button[type=submit]').removeAttr("clicked");
            $(this).attr("clicked", "true");
        });

    }

    /**
     * Set one use url
     * @param url 
     */
    public setOneUseUrl( url:string ){
        
        this.request.setOptionValue( 'url' , url );

    }

    /**
     * Resets the url to the default one.
     */
    public resetOneUseUrl(){

        this.request.setOptionValue( 'url' , this.url );

    }

    /**
     * Set options
     * @param options
     */
    public setOption( options:JQuery.AjaxSettings ){

        this.request.setOption(options);

    }

    /**
     * Set a specific key in options
     * @param key
     * @param value
     */
    public setOptionValue( key:string, value:string ){

        this.request.setOptionValue(key,value);

    }

    /** 
     * Returns the current action url
    */
    public getAction(){

        return this.request.getOptions().url;

    }

    /** 
     * This function is used to avoid loader height overflow
     * when hiding or showing elements on the page which might
     * change the screen size. With this called it the loader
     * overlay will be resized to the new size.
    */
    public resizeLoader(){
        this.loader.resize();
    }

    /**
     *
     * @returns {Loader}
     */
    public getLoader(){
        return this.loader;
    }

    /**
     * A function to be called if the request succeed
     * @param successCallback 
     */
    public addSuccessEvent( successCallback: JQuery.Ajax.SuccessCallback<Object> ){
        return this.request.addSuccessEvent( successCallback );
    }

    /**
     * A function to be called if the request success
     * and returns with success == 1.
     * If success is not 1, then we show the error messages if have any
     * Otherwise show some general error message
     * @param validationSuccessCallback 
     */
    public addValidationSuccessEvent( validationSuccessCallback: ( data: any, textStatus: JQuery.Ajax.SuccessTextStatus, jqXHR: JQuery.jqXHR) => void ){
        
        this.request.addSuccessEvent( ( data, textStatus, jqXHR ) => {

            if(data.success == 1 ) {
                validationSuccessCallback( data, textStatus , jqXHR );
                if( data.redirectUrl != undefined ){
                    location.href = data.redirectUrl;
                }
                return;
            }


            
            if( typeof data.error == 'object' ){
                this.handleErrors( data.error , (typeof data.composite !== 'undefined' && data.composite == true) ? true : false);
                return;
            }

            Framework.throwErrorModal();

            return;
        });

    }

    /**
     * A function to be called if the request fails
     * @param errorCallback 
     */
    public addErrorEvent( errorCallback: JQuery.Ajax.ErrorCallback<Object> ){

        return this.request.addErrorEvent( errorCallback );
        
    }

    /**
     * A function to be called when the request finishes (after success and error callbacks are executed).
     * @param completeCallback 
     */
    public addCompleteEvent( completeCallback: ( data: any, textStatus: JQuery.Ajax.SuccessTextStatus, jqXHR: JQuery.jqXHR) => void ){

        this.request.addCompleteEvent( ( data, textStatus , jqXHR ) => {
            completeCallback( data, textStatus , jqXHR );
            this.loader.stop();
        });

    }

    /**
     * This will submit's the ajax request
     */
    public submit( type: string = null ){

        this.clearErrors();

        if( this.isDisabled ) return;

        if( this.debug === false ){
            this.loader.start();
        }
        
        let indexedArray:any = this.getFormDataArray();

        if( type !== null )
           indexedArray[type] = true;

        if( this.serializeArray ){
            indexedArray = JSON.stringify(this.getFormDataArray());
        }

        this.request.submit( indexedArray );
        
    }

    public getFormData(){
        return this.$formElement.serializeArray();
    }

    public getFormDataArray(){

        this.lastData = this.getFormData();

        this.lastDataArray = {};

        for( let i = 0 ; i < this.lastData.length ; i++ ){
            let value:any = this.lastData[i];
            let name:any = value['name'];
            let val = value['value'];
            name = name.substring(name.lastIndexOf("[")+1,name.lastIndexOf("]"));

            this.lastDataArray[name] = Util.parseIfJson( val );

            if( this.lastDataArray[name] !== val  && val == '{"_labels":[]}' ){
                this.lastData[i]['value'] = '';
            }
        }

        let indexedArray:any = {};

        $.map(this.lastData, function(n, i){
            indexedArray[n['name']] = n['value'];
        });

        return indexedArray;
    }

    /**
     * Shows the validation errors
     * in the appropriate input error container
     *
     *
     * FIXME: This composite method will be bad if we have columns with the same name in multiple entities
     *
     * @param {Array<any>} errors
     * @param {boolean} isComposite
     */
    public handleErrors( errors:Array<any> , isComposite = false){

        if( isComposite ){

            $.each( errors ,(key,value)=>{
                this.handleErrors(<any>value);
            });

            return;
        }

        for (let key in errors) {

            
            let formControl:JQuery<HTMLElement> = $( this.$formElement.find('[name*="'+key.toString()+']"]')[0]);


            if( formControl.prop("type") === 'checkbox' ){
                this.errorInputGroups.push( formControl.parent().parent().parent().addClass('has-error') );
            }else if( formControl.length > 0 ){
                let errorHtml = '';
                let sub = false;

                if( formControl.prop("type") === 'radio' ){
                    this.errorInputGroups.push(formControl.parents('.form-group').addClass('has-error'));
                    errorHtml += '<li>'+errors[key].toString()+'</li>';
                }else if( typeof errors[key] === 'object' ){

                    let inputName = formControl.prop('name');
                    let inputContainer:JQuery<HTMLElement>;
                    sub = true;

                    for( let subKey in errors[key] ){

                        if( typeof subKey == 'string' && typeof errors[key][subKey] == 'object' ){

                            let errorHtml = '<div class="help-block with-errors"><ul class="list-unstyled">';

                            for( let msgKey in errors[key][subKey] ){

                                errorHtml += '<li>' + errors[key][subKey][msgKey].toString() + '</li>';

                            }

                            errorHtml += '</ul></div>';

                            let inputNameASD:any = 'input[name="'+inputName+'"]';

                            if( formControl.prop('name') == inputName  && !Util.isInt(subKey)){
                                inputName = inputName.replace('[' + subKey +']','');
                                inputNameASD = 'input[name="'+inputName + '['  + subKey + ']' +'"]';
                            }else{
                                inputName = inputName.replace('[0]','['+subKey.toString()+']');
                                inputNameASD = 'input[name="'+inputName+'"]';
                            }
                            if( typeof inputContainer == 'undefined' ){
                                inputContainer = formControl.parent();
                            }

                            let el = inputContainer.parents('.form-group').find(inputNameASD);

                            if( el.parent().hasClass('date') ){
                                this.errorInputGroups.push( el.parent().parent().addClass('has-error') );
                            }else{
                                this.errorInputGroups.push( el.parent().addClass('has-error') );
                            }

                            if( el.parent().hasClass('date') ){
                                el.parent().parent().append( errorHtml );
                            }else{
                                el.parent().append( errorHtml );
                            }

                        }else {
                            if( formControl.parent().hasClass('input-group') ){
                                this.errorInputGroups.push( formControl.parent().parent().addClass('has-error') );
                            }else{
                                this.errorInputGroups.push( formControl.parent().addClass('has-error') );
                            }
                            errorHtml += '<li>' + errors[key][subKey].toString() + '</li>';
                        }

                    }

                }else{
                    this.errorInputGroups.push(formControl.parents('.form-group').addClass('has-error'));
                    errorHtml += '<li>'+errors[key].toString()+'</li>';
                }

                if( errorHtml !== '' ) {
                    errorHtml = '<div class="help-block with-errors"><ul class="list-unstyled">' + errorHtml;
                    errorHtml += '</ul></div>';
                    if( formControl.prop("type") === 'radio' ){
                        console.log(formControl);
                        formControl.parent().parent().parent().parent().append(errorHtml);
                    }else if (formControl.parent().hasClass('input-group')) {
                        formControl.parent().parent().append(errorHtml);
                    } else {
                        formControl.parent().append(errorHtml);
                    }
                }
                
            }

        }

        if( this.debug ){
            console.log( 'Form backend validation error! Form name: ' + this.$formElement.prop('id') + ', Action: ' + this.options.url , errors );
        }

        window.setTimeout(function () {
            var errors = $('.has-error')
            if (errors.length) {
                $('html, body').animate({ scrollTop: errors.offset().top - 150 }, 500);
            }
        }, 0);

        this.loader.resize();
        
    }

    /**
     * This will fill the form with data
     *
     * @param data
     * @param {string} name
     */
    public populate( data:any , name:string = ''){

        let self = this;

        $.each(data, function(key, value) { 
            let inputName = ( name.length > 0 ) ? name + "[" + key + "]" : key;
            let ctrl = $('[name="'+ inputName +'"]', self.$formElement);

            switch(ctrl.prop("type")) { 
                case "radio": 
                case "checkbox":   
                    ctrl.each(function() {
                        if($(this).is(':checked') != value){
                            $(this).trigger('click');
                        }
                        $(this).trigger('change');
                    });
                    break;
                case "hidden":
                    let select = ctrl.parent().find('select');
                    if( select ){
                        let labels:any = value._labels;
                        if( Object.keys(labels).length > 1 ){
                            $.each( value , function( autocompleteKey, autocompleteValue:any ){
                                if( key == '_labels' ) return;
                                Form.populateSelect2( select , autocompleteValue , labels[autocompleteKey] );
                            } );
                        }else{
                            Form.populateSelect2( select , value[0] , labels[0] );
                        }
                        break;
                    }
                default:
                    ctrl.val(value).trigger('change'); 
            }  
        });  

    }

    /**
     *
     * @param {JQuery<HTMLElement>} select
     * @param {string} id
     * @param {string} text
     */
    public static populateSelect2( select:JQuery<HTMLElement>, id:string, text:string ){
        if (select.find("option[value='" + id + "']").length) {
            select.val(id).trigger('change');
        } else { 
            // Create a DOM Option and pre-select by default
            let newOption = new Option(text, id, true, true);
            // Append it to the select
            select.append(newOption).trigger('change');
        } 
    }

    /**
     * Crear all inputs from error messages
     */
    public clearErrors(){

        for( let i = 0; i < this.errorInputGroups.length; i++ ){
            this.errorInputGroups[i].removeClass('has-error');
            this.errorInputGroups[i].find('.with-errors').remove();
        }

        this.errorInputGroups = [];

    }

    public getLastData() : Array<Object> {
        return this.lastData;
    }

    public getLastDataArray() : Object {
        return this.lastDataArray;
    }

    /**
     * 
     */
    public getElement(): JQuery<HTMLElement>{
        return this.$formElement;
    }

    /**
     * This function will clear the input fields
     */
    public clear(){
        this.clearErrors();

        this.$formElement
            .find("input[type=text],textarea,select,input[type=password]")
                .val('')
                .end()
            .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end()
            .find(".select2-autocomplete-field").val([]).trigger('change');
    }

    /**
     * Disable the form, so it can never be called again
     */
    public disable(){

        this.isDisabled = true;

    }

}