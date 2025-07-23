/**
 * Common code comes here
 */
import * as $ from 'jquery';
import DatePicker from './components/atoms/DatePicker';
import Autocomplete from './components/atoms/Autocomplete';
import ScrollTo from './libs/behavioural/ScrollTo';
import MobileMenu from './components/organisms/MobileMenu';
import PageLoader from './pages/PageLoader';
import Tooltip from './components/atoms/Tooltip';
import Collection from "./components/atoms/Collection";
import MainMenu from "./components/organisms/MainMenu";
import {CookieLawAccept} from "./libs/util/CookieLawAccept";


$(function(  ){
    DatePicker.init();
    ScrollTo.initializeAllLinks();
    let autocomplete:Autocomplete = new Autocomplete();
        autocomplete.initGroups();

    let mobileMenu:MobileMenu = new MobileMenu();
    let mainMenu:MainMenu = new MainMenu();

    let tooltip:Tooltip = new Tooltip();

    // This will get the current page's typescript and init it.
    let pageLoader:PageLoader = new PageLoader();
    pageLoader.initCurrentPage();

    let collection:Collection = new Collection($('.form-collection-widget'));


    $(document).ready(function(e){

        $('.m-img-check').click(function(e) {
            $('.m-img-check').not(this).removeClass('m-check')
                .siblings('input').prop('checked',false);
            $(this).addClass('m-check')
                .siblings('input').prop('checked',true);
        });

    });

    new CookieLawAccept({
        'htmlMarkup' : $('.eupopup-container').html()
    });

});