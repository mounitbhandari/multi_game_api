<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NumberCombinationController;
use App\Http\Controllers\DrawMasterController;
use App\Http\Controllers\GameTypeController;
use App\Http\Controllers\PlayMasterController;
use App\Http\Controllers\ResultMasterController;
use App\Http\Controllers\SingleNumberController;
use App\Http\Controllers\ManualResultController;
use App\Http\Controllers\Test;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\CommonFunctionController;
use App\Http\Controllers\StockistController;
use App\Http\Controllers\CentralController;
use App\Http\Controllers\NextGameDrawController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\CPanelReportController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TerminalReportController;
use App\Http\Controllers\SuperStockistController;
use App\Http\Controllers\PayOutSlabController;
use App\Http\Controllers\CardCombinationController;
use App\Http\Controllers\DoubleNumberCombinationController;
use App\Http\Controllers\AndarNumberController;
use App\Http\Controllers\BaharNumberController;
use App\Http\Controllers\RechargeToUserController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("login",[UserController::class,'login']);
//Route::get("logout",[UserController::class,'logout']);
//Route::get("logout/{id}",[UserController::class,'logout']);


Route::post("register",[UserController::class,'register']);
Route::get("serverTime",[CommonFunctionController::class,'getServerTime']);
Route::get("backupDatabase",[CommonFunctionController::class,'backup_database']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    //All secure URL's

    Route::get('/me', function(Request $request) {
        return auth()->user();
    });

    Route::get("user",[UserController::class,'getCurrentUser']);
    Route::get("logout",[UserController::class,'logout']);

//    Route::get("logout/{id}",[UserController::class,'logout']);

    //get all users
    Route::get("users",[UserController::class,'getAllUsers']);

    Route::post("prizeValueByTerminalId",[TerminalController::class,'prize_value_by_terminal_id']);

    //single_numbers
    Route::get("singleNumbers",[SingleNumberController::class,'index']);

    //number_combinations
    Route::get("numberCombinations",[NumberCombinationController::class,'index']);
    Route::get("numberCombinations/number/{id}",[NumberCombinationController::class,'getNumbersBySingleNumber']);
    Route::get("numberCombinations/matrix",[NumberCombinationController::class,'getAllInMatrix']);

    //draw_masters
    Route::get('drawTimes',[DrawMasterController::class,'index']);
    Route::get('drawTimes/{id}',[DrawMasterController::class,'get_draw_time_by_game_id']);

    Route::get('drawTimes/dates/{date}',[DrawMasterController::class,'get_incomplete_games_by_date']);

    //game_types
    Route::get('gameTypes',[GameTypeController::class,'index']);

    //manual_result

    Route::post('manualResult',[ManualResultController::class, 'save_manual_result']);
    Route::post('totalSaleOnCurrentDraw',[ManualResultController::class, 'check_total_sale_on_current_draw']);

    //play_masters
    Route::post('buyTicket',[PlayController::class,'save_play_details']);
    Route::post('cancelTicket',[PlayMasterController::class,'cancelPlay']);
    Route::post('claimPrize',[PlayMasterController::class,'claimPrize']);

    Route::get('results/currentDate/{id}',[ResultMasterController::class, 'get_results_by_current_date']);
    Route::get('results/lastResult',[ResultMasterController::class, 'get_last_result']);


    Route::get('stockists',[StockistController::class, 'get_all_stockists']);
    Route::get('stockists/{id}',[StockistController::class, 'get_stockist']);
    Route::post('stockists',[StockistController::class, 'create_stockist']);
    Route::put('stockists',[StockistController::class, 'update_stockist']);
    Route::put('stockists/balance',[StockistController::class, 'update_balance_to_stockist']);

    Route::get('terminals',[TerminalController::class, 'get_all_terminals']);
    Route::post('terminals',[TerminalController::class, 'create_terminal']);
    Route::get('terminal/{id}',[TerminalController::class, 'get_logged_in_terminal']);
    Route::put('terminals',[TerminalController::class, 'update_terminal']);
//    Route::get('terminalLoggedId',[TerminalController::class, 'get_terminal_by_auth']);
    Route::get('updateAutoClaimTerminal/{id}',[TerminalController::class, 'update_auto_claim']);
    Route::put('terminals/balance',[TerminalController::class, 'update_balance_to_terminal']);

    Route::get('cPanel/barcodeReport', [CPanelReportController::class, 'barcode_wise_report']);
    Route::post('cPanel/barcodeReportByDate', [CPanelReportController::class, 'barcode_wise_report_by_date']);
    Route::get('cPanel/barcodeReport/particulars/{id}', [CPanelReportController::class, 'get_barcode_report_particulars']);
    Route::get('cPanel/barcodeReport/prizeValue/{id}', [CPanelReportController::class, 'get_prize_value_by_barcode']);
    Route::get('cPanel/customerSaleReport', [CPanelReportController::class, 'customer_sale_report']);
    Route::post('cPanel/customerSaleReports', [CPanelReportController::class, 'customer_sale_reports']);
    Route::post('terminal/barcodeReport',[TerminalReportController::class, 'barcode_wise_report_by_terminal']);
    Route::post('terminal/terminal_sale_reports', [TerminalReportController::class, 'terminal_sale_reports']);
    Route::post('terminal/terminal_sale_reports_by_gameId', [TerminalReportController::class, 'terminal_sale_reports_by_gameId']);

    Route::post('cPanel/barcodeReportByDate', [CPanelReportController::class, 'barcode_wise_report_by_date']);
    Route::post('stockist/customerSaleReports', [StockistController::class, 'customer_sale_reports']);
    Route::post('stockist/barcodeReportByDate', [StockistController::class, 'barcode_wise_report_by_date']);

    Route::post('terminal/updateCancellation', [TerminalReportController::class, 'updateCancellation']);
    Route::post('terminal/updateCancellation/{id}', [TerminalReportController::class, 'updateCancellationGameWise']);


    Route::put('terminal/resetPassword', [TerminalController::class, 'reset_terminal_password']);

    Route::put('cPanel/game/payout',[GameTypeController::class, 'update_payout']);
    Route::post('getResultByDate', [ResultMasterController::class, 'get_result_by_date']);

    Route::get('getGame', [GameController::class, 'getGame']);
    Route::get('gameTotalReportToday', [GameController::class, 'get_game_total_sale_today']);
    Route::get('getSingleNumber', [SingleNumberController::class, 'get_all_single_number']);


    Route::get('updateAutoGenerate/{id}', [GameController::class, 'update_auto_generate']);
    Route::get('activateGame/{id}', [GameController::class, 'activate_game']);

    Route::post('superStockists',[SuperStockistController::class, 'create_super_stockist']);
    Route::put('superStockists',[SuperStockistController::class, 'update_super_stockist']);
    Route::get('superStockists',[SuperStockistController::class, 'get_super_stockist']);
    Route::get('getStockistBySuperStockistId/{id}',[SuperStockistController::class, 'getStockistBySuperStockistId']);
    Route::put('superStockists/balance',[SuperStockistController::class, 'update_balance_to_super_stockist']);

//    PAYOUT SLABS
    Route::get('payoutSlabs',[PayOutSlabController::class, 'get_all_payout_slabs']);

    Route::post('cPanel/loadReport', [CPanelReportController::class, 'load_report']);

    Route::get('getDoubleNumber', [DoubleNumberCombinationController::class, 'get_all_double_number']);

    Route::post('pinCheckValidation',[UserController::class, 'check_pin']);

    Route::get('getTwelveCards',[CardCombinationController::class, 'get_all_twelve_card']);
    Route::get('getSixteenCards',[CardCombinationController::class, 'get_all_sixteen_card']);

    Route::post('superStockist/customerSaleReports', [SuperStockistController::class, 'customer_sale_reports']);
    Route::post('superStockist/barcodeReportByDate', [SuperStockistController::class, 'barcode_wise_report_by_date']);

    Route::get('getAndarNumbers',[AndarNumberController::class, 'get_all_andar_number']);
    Route::get('getBaharNumbers',[BaharNumberController::class, 'get_all_bahar_number']);

    Route::post('updateBlock',[UserController::class, 'update_block']);
    Route::post('loginApprove',[TerminalController::class, 'approve_login']);
    Route::post('gamePermission',[TerminalController::class, 'game_permission_update']);


    Route::get('getTransaction/{id}', [TransactionController::class, 'getTransaction']);
    Route::post('getRechargeDetails',[RechargeToUserController::class, 'getTransactionByUserForAdmin']);

    Route::post('drawWiseReportToday', [CPanelReportController::class, 'draw_wise_report']);
    Route::post('mailTransaction', [TransactionController::class, 'mailTransactionOneMonth']);

});




Route::group(array('prefix' => 'dev'), function() {

    Route::post('cancelTicket',[PlayMasterController::class,'cancelPlay']);

    Route::get('terminal/{id}',[TerminalController::class, 'get_logged_in_terminal']);

    Route::get('gameTotalReportToday', [GameController::class, 'get_game_total_sale_today']);
    Route::get('deleteDataExceptSevenDays', [CentralController::class, 'delete_data_except_seven_days']);
    Route::get('checkSmallerTotalSale/{last_draw_master_id}', [CentralController::class, 'checkSmallerTotalSale']);
    Route::get('getTransaction/{id}', [TransactionController::class, 'getTransaction']);
    Route::post('mailTransaction', [TransactionController::class, 'mailTransactionOneMonth']);


    Route::post('drawWiseReportToday', [CPanelReportController::class, 'draw_wise_report']);


    Route::get('results/lastResult',[ResultMasterController::class, 'get_last_result']);

    Route::get('claimPrizes',[TerminalController::class, 'claimPrizes']);

    Route::post('totalSaleOnCurrentDraw',[ManualResultController::class, 'check_total_sale_on_current_draw']);



    Route::post('getRechargeDetails',[RechargeToUserController::class, 'getTransactionByUserForAdmin']);
    Route::post('getTransactionByUser',[RechargeToUserController::class, 'getTransactionByUser']);

    Route::get('getTodayResultByGame/{id}',[ResultMasterController::class, 'get_result_today_by_game']);
    Route::get('getTodayResultByGameAsc/{id}',[ResultMasterController::class, 'get_result_today_by_game_Asc']);

    Route::get('getTodayLastResultByGame/{id}',[ResultMasterController::class, 'get_result_today_last_by_game']);

    //card_api
    Route::get('getTwelveCards',[CardCombinationController::class, 'get_all_twelve_card']);
    Route::get('getSixteenCards',[CardCombinationController::class, 'get_all_sixteen_card']);

    Route::post('pinCheckValidation',[UserController::class, 'check_pin']);

    Route::post('loginApprove',[TerminalController::class, 'approve_login']);

    Route::get('seedingData',[NumberCombinationController::class, 'create_migration']);

    Route::get('getAndarNumbers',[AndarNumberController::class, 'get_all_andar_number']);
    Route::get('getBaharNumbers',[BaharNumberController::class, 'get_all_bahar_number']);

    Route::get('terminals',[TerminalController::class, 'get_all_terminals']);

    Route::post('superStockists',[SuperStockistController::class, 'create_super_stockist']);
    Route::get('superStockists',[SuperStockistController::class, 'get_super_stockist']);
    Route::get('getStockistBySuperStockistId/{id}',[SuperStockistController::class, 'getStockistBySuperStockistId']);
    Route::post('stockists',[StockistController::class, 'create_stockist']);
    Route::put('stockists',[StockistController::class, 'update_stockist']);
    Route::post('terminals',[TerminalController::class, 'create_terminal']);
    Route::put('terminals',[TerminalController::class, 'update_terminal']);

    Route::get('payoutSlabs',[PayOutSlabController::class, 'get_all_payout_slabs']);

    Route::get('activateGame/{id}', [GameController::class, 'activate_game']);

    // Route::post('terminal/updateCancellation/{id}', [TerminalReportController::class, 'updateCancellationGameWise']);
    // Route::post('updateDrawOver', [CentralController::class, 'update_is_draw_over']);

//    Route::post('createAutoResult/{id}', [CentralController::class, 'createResult']);
    Route::post('createAutoResult/{id}', [CentralController::class, 'createAutoResult']);

    Route::get('getGame', [GameController::class, 'getGame']);

    Route::post('getResultByDate', [ResultMasterController::class, 'get_result_by_date']);

    Route::post('cPanel/barcodeReportByDate', [CPanelReportController::class, 'barcode_wise_report_by_date']);
    Route::post('cPanel/loadReport', [CPanelReportController::class, 'load_report']);
    Route::post('stockist/customerSaleReports', [StockistController::class, 'customer_sale_reports']);
    Route::post('superStockist/customerSaleReports', [SuperStockistController::class, 'customer_sale_reports']);

    Route::post('stockist/barcodeReportByDate', [StockistController::class, 'barcode_wise_report_by_date']);
    Route::post('superStockist/barcodeReportByDate', [SuperStockistController::class, 'barcode_wise_report_by_date']);

    // Route::get("users",[UserController::class,'getAllUsers']);
    // Route::patch("users",[UserController::class,'update']);

    //single_numbers
    Route::get("singleNumbers",[SingleNumberController::class,'index']);
    Route::get('getSingleNumber', [SingleNumberController::class, 'get_all_single_number']);
    Route::get('getDoubleNumber', [DoubleNumberCombinationController::class, 'get_all_double_number']);


    //number_combinations
    // Route::get("numberCombinations",[NumberCombinationController::class,'index']);
    // Route::get("numberCombinations/number/{number}",[NumberCombinationController::class,'getNumbersBySingleNumber']);
    Route::get("numberCombinations/matrix",[NumberCombinationController::class,'getAllInMatrix']);

    //draw_masters
    Route::get('drawTimes',[DrawMasterController::class,'index']);
    Route::get('drawTimes/{id}',[DrawMasterController::class,'get_draw_time_by_game_id']);
    Route::get('drawTimes/active',[DrawMasterController::class,'getActiveDraw']);
    Route::get('drawTimes/active/{id}',[DrawMasterController::class,'getGameActiveDraw']);
    Route::get('drawTimes/dates/{id}',[DrawMasterController::class,'get_incomplete_games_by_date']);

    //game_types
    Route::get('gameTypes',[GameTypeController::class,'index']);

    //play_masters
     Route::post('buyTicket',[PlayController::class,'save_play_details']);

    //game
    // Route::get('playDetails/playId/{id}',[PlayMasterController::class,'get_play_details_by_play_master_id']);

    //result_masters
    Route::get('results',[ResultMasterController::class, 'get_results']);
    Route::get('results/{id}',[ResultMasterController::class, 'get_result']);
    Route::post('getResultSheetByCurrentDateAndGameId',[ResultMasterController::class, 'get_result_sheet_by_current_date_and_game_id']);

    //May be in use may not be in use
//    Route::get('results/currentDate/{id}',[ResultMasterController::class, 'get_results_by_current_date']);

        Route::get('results/lastResult',[ResultMasterController::class, 'get_last_result']);


    //manual_result

    // Route::post('manualResult',[ManualResultController::class, 'save_manual_result']);


//    test
     Route::get('test',[Test::class, 'testNew']);


     Route::get('stockists',[StockistController::class, 'get_all_stockists']);
    // Route::get('stockists/{id}',[StockistController::class, 'get_stockist']);
    // Route::post('stockists',[StockistController::class, 'create_stockist']);
    // Route::put('stockists',[StockistController::class, 'update_stockist']);
    // Route::put('stockists/balance',[StockistController::class, 'update_balance_to_stockist']);


    // Route::get('terminals',[TerminalController::class, 'get_all_terminals']);
    // Route::post('terminals',[TerminalController::class, 'create_terminal']);
    // Route::put('terminals',[TerminalController::class, 'update_terminal']);
    // Route::get('terminals/{id}',[TerminalController::class, 'get_stockist_by_terminal_id']);
    // Route::put('terminals/balance',[TerminalController::class, 'update_balance_to_terminal']);


    // Route::post('createAutoResult', [CentralController::class, 'createResult']);

     Route::post('save_auto_result', [ResultMasterController::class, 'save_auto_result']);

    Route::get('nextDrawId', [NextGameDrawController::class, 'getNextDrawIdOnly']);


    Route::get('cPanel/barcodeReport', [CPanelReportController::class, 'barcode_wise_report']);
    Route::get('cPanel/barcodeReport/particulars/{id}', [CPanelReportController::class, 'get_barcode_report_particulars']);
    Route::get('cPanel/barcodeReport/prizeValue/{id}', [CPanelReportController::class, 'get_prize_value_by_barcode']);
    // Route::get('cPanel/customerSaleReport', [CPanelReportController::class, 'customer_sale_report']);
     Route::post('cPanel/customerSaleReports', [CPanelReportController::class, 'customer_sale_reports']);
     Route::post('terminal/terminal_sale_reports', [TerminalReportController::class, 'terminal_sale_reports']);
     Route::post('terminal/terminal_sale_reports_by_gameId', [TerminalReportController::class, 'terminal_sale_reports_by_gameId']);
     Route::post('terminal/barcodeReport',[TerminalReportController::class, 'barcode_wise_report_by_terminal']);


    // Route::put('terminal/resetPassword', [TerminalController::class, 'reset_terminal_password']);

     Route::put('cPanel/game/payout',[GameTypeController::class, 'update_payout']);

    Route::get('getGame', [GameController::class, 'getGame']);

    // Route::get('updateAutoGenerate/{id}', [GameController::class, 'update_auto_generate']);


});

