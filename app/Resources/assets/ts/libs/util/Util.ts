import * as numeral from "numeral";
import * as $ from "jquery";

/**
 * This class contains helper functions for our application
*/
export class Util{

    /**
     * discuss at: http://locutus.io/php/ucfirst/
     * original by: Kevin van Zonneveld (http://kvz.io)
     * bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
     * improved by: Brett Zamir (http://brett-zamir.me)
     * example 1: ucfirst('kevin van zonneveld')
     * returns 1: 'Kevin van zonneveld'
     * 
     * @source http://locutus.io/php/strings/ucfirst/
     */
    public static ucfirst ( str:string ) {
      
        str += ''
        var f = str.charAt(0)
          .toUpperCase()
        return f + str.substr(1)
        
    }

    /**
     * Simple helper function to callbacks on 
     * multiple elements at one.
     * 
     * Example usage:
     * Util.groupAction( 'click' , ()=>{
     *      console.log('hello');
     * }, $('#element1'), $('#element2') );
     * This will console.log when you click on element1 or element2
     * 
     * @param action 
     * @param callback 
     * @param objects 
     */
    public static groupAction( action:string , callback: () => void , ...objects: Array<JQuery<HTMLElement>>){
        objects.forEach(toggler => {
            toggler.on(action, () => {
                callback();
            });
        });
    }

    public static parseIfJson(str:string) {
        try {
            return JSON.parse(str);
        } catch (e) {
            return str;
        }
    }

    public static swap(obj:any, i:number, j:number) {
        let temp = obj[i];
        obj[i] = obj[j];
        obj[j] = temp;
        return obj;
    }

    public static selectionSort(obj:any, key:string) {

        $.each( obj , function( i:number , iValue:object ){
            let min:number = i;
            $.each( obj , function( j:number , jValue:object ){
                let date1 = new Date( obj[j][key] ),
                    date2 = new Date( obj[min][key] );
                if(date1 > date2) {
                    min = j;
                }
                if(i !== min) {
                    //obj = Util.swap(obj, i, min);
                }
            });
        } );
        return obj;
    }

    /**
     * Helper function to use for getting the length
     * of an object. 
     * Exists in case of algorithm change
     * @param object 
     */
    public static objLen( object:Object ){
        return Object.keys(object).length;
    }

    /**
     * Helper to toggle disable on element in one call
     * @param element 
     * @param disabled 
     */
    public static disableElement( element:JQuery<HTMLElement>, disabled:boolean = true ){
        if( disabled ){
            element.attr('disabled', 'disabled' );
        }else{
            element.removeAttr('disabled');
        }
    }

    /**
     * Simple helper will return a readable price string
     * @param {number} price
     * @returns {string}
     */
    public static getReadablePrice( price:number ){
        return numeral( price ).format('0,0').replace(',',' ');
    }

    /**
     *
     * @param {string} target
     * @param {string} search
     * @param {string} replacement
     * @returns {string}
     */
    public static replaceAll(target: string, search:string , replacement:string ) {
        return target.split(search).join(replacement);
    }

    /**
     *
     * @param labels
     * @param {string} str
     * @param {string} prefix
     * @param {string} suffix
     * @returns {string}
     */
    public static concatArray( labels:any , str:string , prefix:string = '', suffix: string = '' ){

        let retString = '';

        if( labels == undefined ) return retString;

        labels.forEach( ( value:any ) => {

            retString+= ( retString.length > 0 ) ? str : '';
            retString+= value;

        } );

        return ( ( labels.length > 0 ) ? prefix : '' ) +  retString + ( ( labels.length > 0 ) ? suffix : '' );

    }


    public static concatArrayValueLabels( array:any , value1:string, value2:string, str:string = ', ', prefix:string = '' , suffix: string ='' ){

        let retString = '';

        if( array == undefined ) return retString;

        $.each( array , (key:number, value:any) => {

            if( typeof value[value1]._labels != 'undefined' ){

                retString+= ( retString.length > 0 ) ? str : '';

                let suffix = '';

                if( typeof value[value2]._labels != 'undefined' ){
                    suffix+= Util.concatArray( value[value2]['_labels'] , str , '(' , ')' );
                }
                retString+= Util.concatArray( value[value1]['_labels'] , str ) + suffix;
            }
        } );

        return ( ( array.length > 0 ) ? prefix : '' ) +  retString + ( ( array.length > 0 ) ? suffix : '' );

    }

    public static concatArrayValueLabelsIntoList( array:any , value1:string, value2:string, str:string = ', ', prefix:string = '' , suffix: string ='' ){

        let retList:any = [];

        if( array == undefined ) return retList;

        $.each( array , (key:number, value:any) => {

            if( typeof value[value1]._labels != 'undefined' ){

                let arr:string = value[value1]._labels;

                if( typeof value[value2]._labels != 'undefined' ){
                    arr += Util.concatArray( value[value2]['_labels'] , str , '(' , ')' );
                }

                retList.push(arr);

            }

        } );

        return Util.formArrayToList(retList, 'value', str, prefix, suffix);

    }

    /**
     *
     * @param array
     * @param {string} key
     * @param {string} concatStr
     * @param {string} concatPrefix
     * @param {string} concatSuffix
     * @returns {string}
     */
    public static formArrayToList( array:any , key:string = 'value' , concatStr?:string, concatPrefix:string = '', concatSuffix:string = ''){
        let lis = '';

        if( typeof array == 'undefined' ) return lis;
        for( let i = 0 ; i < Util.objLen(array) ; i++ ){

            let val = '';

            if( typeof array[i][key] == 'object' && array[i][key].length > 0 ) {
                val = array[i][key];
                val = Util.concatArray( val , concatStr , concatPrefix , concatSuffix );
            }
            else if( typeof array[i][key] == 'string' && array[i][key].length > 0 ) {
                val = array[i][key];
            }
            else if( typeof array[i] != 'undefined' && array[i].length > 0 ) {
                val = array[i];
            }

            if( val.length > 0 ){
                lis += '<li>' + val + '</li>';
            }

        }

        return lis;
    }

    public static isInt(value:any) {
        return !isNaN(value) &&
            parseInt(Number(value).toString()) == value &&
            !isNaN(parseInt(value, 10));
    }

}