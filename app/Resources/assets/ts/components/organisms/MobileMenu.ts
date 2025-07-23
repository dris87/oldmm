import * as $ from 'jquery';

/**
 *
 */
export default class MobileMenu{

    /**
     * @type {string}
     */
    private prefix:string = 'm-mobile-menu--';

    /**
     * Our mobile menu toggler burger element
     */
    private burger:JQuery<HTMLElement>;

    /**
     * Our search icon container
     */
    private search:JQuery<HTMLElement>;
    /**
     * Our search icon container
     */
    private searchContainer:JQuery<HTMLElement>;

    /**
     * Our mobile menu header element
     */
    private header:JQuery<HTMLElement>;

    /**
     *
     */
    public constructor( ){

        this.burger = $('.' + this.prefix + 'burger-container');
        this.search = $('.' + this.prefix + 'search');
        this.searchContainer = $('.' + this.prefix + 'search-container');
        this.header = $('.' + this.prefix + 'header');
        let body = $('body');

        this.burger.on('click', () => {
            if( body.hasClass(this.prefix + 'search-opened') ){
                $('body').removeClass(this.prefix + 'search-opened');
            }else {
                body.toggleClass(this.prefix + 'opened');
            }
        });

        this.search.on('click',() => {
            if( body.hasClass(this.prefix + 'opened') ) {
                $('body').removeClass(this.prefix + 'opened');
            }else{
                body.toggleClass(this.prefix + 'search-opened');
            }
        });

        // Kind of hacky, but the same form could not initialize autocompletes on both forms.
        // maybe later find the solution
        $.each( this.searchContainer.find('[name*=mobile-search]') , function () {
            $(this).attr('name', $(this).attr('name').replace('mobile-search','search') );
        });
        
    }

}
