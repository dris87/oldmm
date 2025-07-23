/**
 * Date picker class.
 * 
 * We can use this for initing all of our datepicker
 * inputs in our forms
 * 
 * Default options will init all datepickers on init
 * 
 * @Website: http://eonasdan.github.io/bootstrap-datetimepicker
 * @Github:  https://github.com/Eonasdan/bootstrap-datetimepicker
 */ 

import 'eonasdan-bootstrap-datetimepicker';

/**
 * 
 */
export default class DatePicker{

    /**
     * By defining all default options, we can make sure
     * that on version change we will not have default
     * value changes(hence unwanted behaviour)
     */
    private static options:IOptions = {
        icons : {
            time    : 'fa fa-clock-o',
            date    : 'fa fa-calendar',
            up      : 'fa fa-chevron-up',
            down    : 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next    : 'fa fa-chevron-right',
            today   : 'fa fa-check-circle-o',
            clear   : 'fa fa-trash',
            close   : 'fa fa-remove'
        },
        locale: 'hu',
        allowInputToggle: true
    }

    public constructor(){

    }

    static init( selector:string = '[data-toggle="datetimepicker"]', additionalOptions:IOptions = {}){
        
        let options = (<any>Object).assign( DatePicker.options , additionalOptions );

        $( selector ).datetimepicker( (<any>Object).options );

    }
    // todo: Handle events

}

/**
 * Possible options to set
 */
interface IOptions{
    icons               ?: Object,
    format              ?: String|false,
    dayViewHeaderFormat ?: String|false,
    extraFormats        ?: false|Array<String>,
    stepping            ?: Number,
    minDate             ?: String|false,
    maxDate             ?: String|false,
    useCurrent          ?: Boolean,
    collapse            ?: Boolean,
    locale              ?: String,
    defaultDate         ?: String|false,
    disabledDates       ?: String|Array<String>,
    enabledDates        ?: String|Array<String>,
    tooltips            ?: Object,
    useStrict           ?: Boolean,
    sideBySide          ?: Boolean,
    daysOfWeekDisabled  ?: Array<Number>,
    calendarWeeks       ?: Boolean,
    viewMode            ?: String,
    toolbarPlacement    ?: String,
    showTodayButton     ?: Boolean,
    showClear           ?: Boolean,
    showClose           ?: Boolean,
    widgetPositioning   ?: Object,
    widgetParent        ?: null|JQuery,
    ignoreReadonly      ?: false,
    keepOpen            ?: Boolean,
    focusOnShow         ?: Boolean,
    inline              ?: Boolean,
    keepInvalid         ?: Boolean,
    datepickerInput     ?: String,
    keyBinds            ?: Object,
    debug               ?: Boolean,
    allowInputToggle    ?: Boolean,
    disabledTimeIntervals?: Boolean,
    disabledHours       ?: Boolean,
    enabledHours        ?: Boolean,
    viewDate            ?:Boolean
}