import {Firm} from "../../libs/Firm";
import * as $ from "jquery";

export default class FirmCandidateList{

    public constructor(){

        let firm:Firm = new Firm();
        firm.initCvUnlock($("#firm-candidate-list"));

    }
}