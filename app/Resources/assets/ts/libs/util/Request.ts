import * as $ from 'jquery';
import { SystemConfig } from '../SystemConfig';
import { setTimeout } from 'timers';
import {Framework} from "../Framework";

 /**
 * This class will grab a form jquery opbject and submits it.
 * It will handle the loading animation, and the input blocking on requests
 */
export class Request{

    /**
     * In debug = true, we do not show loading
     * and console.log the data
     */
    public debug:boolean = false;

    /**
     *
     * @type {boolean}
     * @private
     */
    private _isRequesting:boolean = false;

    /** 
     * True on resutiesting
    */
    public isRequesting(){
        return this._isRequesting;
    }

    /**
     * Any default ajax options here
     * @type {{}}
     */
    private options:JQuery.AjaxSettings = {
        method: 'POST'
    };

    /**
     * 
     * @param $formElement
     * @param options 
     */
    public constructor( options:JQuery.AjaxSettings ){

        this.debug = SystemConfig.debug;

        this.setOption( options );

        this.addErrorEvent(()=>{});
        this.addCompleteEvent(()=>{});

    }

    /**
     * Set options
     * @param options
     */
    public setOption( options:JQuery.AjaxSettings ){

        this.options = (<any>Object).assign( this.options , options );

    }

    /**
     * Set a specific key in options
     * @param key 
     * @param value 
     */
    public setOptionValue( key:string, value:string ){

        let opts:any = this.options;
        opts[key] = value;
        this.setOption( opts );
        
    }

    public getOptions(){
        return this.options;
    }

    /**
     * A function to be called if the request succeed
     * @param successCallback 
     */
    public addSuccessEvent( successCallback: JQuery.Ajax.SuccessCallback<Object> ){
        this.options.success = successCallback;
    }

    /**
     * A function to be called if the request fails
     * @param errorCallback 
     */
    public addErrorEvent( errorCallback: JQuery.Ajax.ErrorCallback<Object> ){

        let self = this;

        this.options.error = function (this: any, jqXHR: any, textStatus: any){
            
            errorCallback.call( self, this , jqXHR , textStatus );

            return;

        };
        
    }

    /**
     * A function to be called when the request finishes (after success and error callbacks are executed).
     * @param completeCallback 
     */
    public addCompleteEvent( completeCallback: ( data: any, textStatus: JQuery.Ajax.SuccessTextStatus, jqXHR: JQuery.jqXHR) => void ){

        let self = this;

        this.options.complete = function (this: any, jqXHR: any, textStatus: any){
                      
            setTimeout( () => {
                self._isRequesting = false;
            }, 200);

            if( self.debug ){
                console.log( 'Request response! Action: ' + self.options.url , this, jqXHR, textStatus );
            }

            completeCallback.call( this, self , jqXHR , textStatus );

        };
    }

    public submit( data:Object = {} ){

        this.options.data = data;

        // Only submit if we are not during request
        if( this._isRequesting === false ){
            this._isRequesting = true;
            return $.ajax( this.options );
        }else{
            if( this.debug === true ){
                console.log("Cannott submit during request! ");
            }
            return;
        }

    }


}