import * as $ from 'jquery';

export default class MainMenu{

    /**
     * @type {string}
     */
    private prefix:string = 'm-main-menu--';

    /**
     * @type {JQuery<HTMLElement>}
     */
    private nav:JQuery<HTMLElement>;

    /**
     * @type {string}
     */
    private dropdownSelector:string;

    /**
     * @type {string}
     */
    private activeDropdownClassSelector:string;

    /**
     *
     */
    public constructor( ){

        let self = this;
        this.nav = $('.' + this.prefix.replace('--','') );
        this.dropdownSelector = this.prefix + 'dropdown > a';
        this.activeDropdownClassSelector = this.prefix + 'dropdown--active';
        this.nav.on('click', '.' + this.dropdownSelector, function(e){
            $(this).parent().toggleClass( self.activeDropdownClassSelector );
            e.preventDefault();
            e.stopPropagation();
        });

    }

}
