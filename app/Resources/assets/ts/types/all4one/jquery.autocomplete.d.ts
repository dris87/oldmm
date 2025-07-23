/// <reference types="jquery"/>
// Todo: Nem volt type definíciója, szóval ha van idő akkor össze kellene dobni ide. addig is üresen mehet
interface All4OneAutocompleteOptions {

}

interface All4OneAutocomplete {
    (options?: All4OneAutocompleteOptions): JQuery;
    (methodName?: string, ...params: any[]): JQuery;
}

interface JQuery {
    all4oneAutocomplete: All4OneAutocomplete;
}