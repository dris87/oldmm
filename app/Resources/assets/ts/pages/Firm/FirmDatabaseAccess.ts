import {Firm} from "../../libs/Firm";
import * as $ from "jquery";

export default class FirmDatabaseAccess{

    public constructor(){

        let firm:Firm = new Firm();
        firm.initCvUnlock($("#firm-database-access"));

    }
}