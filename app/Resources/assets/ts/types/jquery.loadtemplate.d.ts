/// <reference types="jquery"/>  
// Todo: Nem volt type definíciója, szóval ha van idő akkor össze kellene dobni ide. addig is üresen mehet
// options itt: https://github.com/codepb/jquery-template
interface loadTemplateOptions {
    append: boolean
}

interface loadTemplate {
    (options?: loadTemplateOptions): JQuery;
    (methodName?: string, ...params: any[]): JQuery;
}

interface JQuery {
    loadTemplate: loadTemplate;
}