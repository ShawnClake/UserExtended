/*
 * General.js
 * Javascript included on every UE component
 */

var UE = UE || {};
UE.Logger = UE.Logger || {};
UE.Performance = UE.Performance || {};

UE.Config = {
    'Debug': true
};

$(function() {
    UE.Performance.T0 = performance.now();

    if(UE.Config.Debug)
        console.log("User Extended GeneralJS loading...");

    UE.Logger.Instance = console.log;

    if(!UE.Config.Debug)
        UE.Logger.Disable();

    UE.Performance.T1 = performance.now();

    console.log("User Extended GeneralJS loaded in " + (UE.Performance.T1 - UE.Performance.T0).toFixed(3) + "ms");
});

UE.Logger.Enable = function() {
    console.log = UE.Logger.Instance;
};

UE.Logger.Disable = function() {
    console.log = function() {};
};