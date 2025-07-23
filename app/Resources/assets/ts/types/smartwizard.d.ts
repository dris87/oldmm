/// <reference types="jquery"/>  
  interface smartWizardOptions {
    next?: string;
    previous?: string;
    finish?: string;
    cancel?: string;

    selected?: number,  // Initial selected step, 0 = first step 
    keyNavigation?: boolean, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
    autoAdjustHeight?: boolean, // Automatically adjust content height
    cycleSteps?: boolean, // Allows to cycle the navigation of steps
    backButtonSupport?: boolean, // Enable the back button support
    useURLhash?: boolean, // Enable selection of the step based on url hash
    lang?: object,
    toolbarSettings?: object,
    anchorSettings?: object,            
    contentURL?: null|string, // content url, Enables Ajax content loading. can set as data data-content-url on anchor
    disabledSteps?: Array<string>,    // Array Steps disabled
    errorSteps?: Array<string>,    // Highlight step with errors
    theme?: string,
    transitionEffect?: string, // Effect on navigation, none/slide/fade
    transitionSpeed?: number
  
    // Triggers when leaving a step.
    // This is a decision making event. 
    // based on its function return value (true/false) 
    // the current step navigation can be cancelled.
    leaveStep?: (objectOfStepAnchorElement: object, indexOfStep: number, directionOfNavigation: string) => any;
    // Triggers when showing a step.
    showStep?: (objectOfStepAnchorElement: object, indexOfStep: number, directionOfNavigation: string) => any;
    // Triggers when reset action starts.
    // This is a decision making event. 
    // based on its function return value (true/false) the reset action can be cancelled.
    beginReset?: () => any;
    // Triggers when reset action ends.
    endReset?: (nameOfTheme: string) => any;
    // Triggers when theme is changed.
    themeChanged?: () => any;
  }
  
  interface smartWizard {
    (options?: smartWizardOptions): JQuery;
    (methodName?: string, ...params: any[]): JQuery;
  }
  
  interface JQuery {
    smartWizard: smartWizard;
  }