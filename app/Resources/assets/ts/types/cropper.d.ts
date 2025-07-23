/// <reference types="jquery"/>
interface cropperOptions {
    append: boolean
}

interface cropperjs {
    (options?: cropperOptions): JQuery;
    (methodName?: string, ...params: any[]): JQuery;
}

interface JQuery {
    cropper: cropperjs;
}