import '../../../libraries/jquery-easy-loading/dist/jquery.loading';

/**
 *  This method will toggle the loading screen on a specific container
 */
export class Loader{

    /**
     * Each loader hide will delay with this amount (ms)
     */
    public delay:number = 0;

    private $loader:JQuery<HTMLElement>;

    /**
     * Spinner template
     */
    private static loaderTemplate:string = `
        <div class="spinner">
            <div class="double-bounce1"></div>
            <div class="double-bounce2"></div>
        </div>
    `;

    private options:JQueryEasyLoading.Options = {
        stoppable : false,
        message: Loader.loaderTemplate,
        zIndex: 999999,
        start: false
    };

    public getOptions(): JQueryEasyLoading.Options{
        return this.options;   
    };

    /**
     * 
     * @param $element 
     * @param additionalOptions 
     */
    public constructor( $element:JQuery<HTMLElement>, additionalOptions:JQueryEasyLoading.Options = {} ){

        this.options = (<any>Object).assign( this.options , additionalOptions );

        this.$loader = $element.loading( this.options );

    }

    /**
     * This will start the loading
     */
    public start(){
        this.$loader.loading('start');
    }

    /**
     * Recalculate and apply new dimensions and position to the overlay,
     * based on the state of the target element. 
     * Call this method if the the overlay is not being shown on the right position and/or dimensions. 
     */
    public resize(){
        this.$loader.loading('resize');
    }

    /**
     * This will stop the loading
     */
    public stop(){
        this.delayCall( ()=> { this.$loader.loading('stop') } );
    }

    /**
     * This will toggle the loading
     */
    public toggle(){
        this.$loader.loading('toggle');
    }
    
    /**
     * Triggered when the loading state is started. Receives the loading object as parameter. 
     */
    public onStart( onStartCallback: ( event: any, loadingObj: any ) => void ){
        this.$loader.on('loading.start', onStartCallback );
    }

    /**
     * Triggered when the loading state is stopped. Receives the loading object as parameter.  
     */
    public onStop( onStopCallback: ( event: any, loadingObj: any ) => void ){
        this.$loader.on('loading.stop', onStopCallback );
    }

    /**
     * Triggered when the overlay element is clicked. Receives the loading object as parameter. 
     */
    public onClick( onClickCallback: ( event: any, loadingObj: any ) => void ){
        this.$loader.on('loading.click', onClickCallback );
    }

    /**
     * Check if the element is loading
     */
    public isLoading(){
        return this.$loader.is(':loading');
    }

    /**
     * Get all the elements with the loading state on
     * NOTE: This will return the jQuery element 
     *       and not a Loader instance!
     */
    public static getAllLoadingElements(){
        return $(':loading');
    }


    /**
     * This method will the call callback delayed
     * 
     * @param callback 
     */
    private delayCall( callback: () => void ){
        
        if( typeof this.delay === 'number' && this.delay > 0 ){

            setTimeout( () => {
                
                callback.call( this );

            } , this.delay );

            return;

        }
        
        callback.call( this );

    }

}