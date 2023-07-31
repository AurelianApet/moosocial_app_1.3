package com.moosocial.moosocialapp.presentation.view.activities;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.support.design.widget.NavigationView;
import android.support.design.widget.TabLayout;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.view.menu.MenuBuilder;
import android.support.v7.widget.PopupMenu;
import android.support.v7.widget.SearchView;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.SubMenu;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.CookieSyncManager;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.AdapterView;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.ImageRequest;
import com.android.volley.toolbox.NetworkImageView;
import com.google.android.gms.ads.AdListener;
import com.google.android.gms.ads.AdRequest;
import com.google.android.gms.ads.InterstitialAd;
import com.google.gson.internal.LinkedTreeMap;
import com.moosocial.moosocialapp.MooApplication;
import com.moosocial.moosocialapp.domain.Me;
import com.moosocial.moosocialapp.R;
import com.moosocial.moosocialapp.domain.interactor.LoadingOwnerAvatar;
import com.moosocial.moosocialapp.domain.interactor.NotificationUpdate;
import com.moosocial.moosocialapp.presentation.model.NotificationModel;
import com.moosocial.moosocialapp.presentation.presenter.BaseMAHWVPresenter;
import com.moosocial.moosocialapp.presentation.view.items.menubar.MenuBarItem;
import com.moosocial.moosocialapp.presentation.view.items.menubar.MenuBarSpinnerAdapter;
import com.moosocial.moosocialapp.presentation.view.items.menubar.MooSpinner;

import com.moosocial.moosocialapp.util.MediaUtility;
import com.moosocial.moosocialapp.util.UtilsConfig;
import com.moosocial.moosocialapp.util.MooGlobals;
import com.moosocial.moosocialapp.util.UtilsWebViewJS;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.xwalk.core.JavascriptInterface;
import org.xwalk.core.XWalkResourceClient;
import org.xwalk.core.XWalkUIClient;
import org.xwalk.core.XWalkView;

import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
public class BaseMooActivityHasWebView extends MooActivityToken {
	public final String TAG = "BaseMooActivityHasWebView";
    protected DrawerLayout mDrawerLayout;
    protected NavigationView navigationView;
    protected MenuBuilder mMenus;
    protected UtilsConfig configApp;
    protected HashMap<String,JSONObject> hMenus = new HashMap<String,JSONObject>();
    protected HashMap<String,JSONObject> hAccountMenus = new HashMap<String,JSONObject>();
    protected TabLayout tabLayout;
    protected Toolbar toolbar;
    protected MooSpinner mSpinner;
    protected Integer bFirstSpin;
    protected Boolean bCheckLoadUrl;
    protected ActionBar ab;
    protected int mNotificationsCount = 0;
    protected PopupMenu pAccount;
    protected ProgressBar mProgressBar;
    protected SwipeRefreshLayout swipeRefresh;
    protected WebView wWebView;
    protected XWalkView xWalkWebView;
    protected ValueCallback mFilePathCallback;
    protected LoadingOwnerAvatar loadingOwerAvatar;
    protected String sCurrentUrl = "";
    private SearchView searchView;
	protected BaseMAHWVPresenter basePresenter;
    protected NotificationUpdate nUpdate;
    private Handler mHandler;
    protected Boolean bCheckNoLoadLogiUrl;
    protected MenuItem prevMenuItem;
    Boolean loadingFinished = true;
    Boolean redirect = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        bCheckNoLoadLogiUrl = false;
        super.onCreate(savedInstanceState);
        configApp = MooGlobals.getInstance().getConfig();
		basePresenter = new BaseMAHWVPresenter(this);

        nUpdate = new NotificationUpdate((MooApplication)getApplication(),this);
        mHandler = new Handler();
        mStatusChecker.run();

        bFirstSpin = 0;
    }

    public BaseMAHWVPresenter getPresenter()
    {
        return basePresenter;
    }

    public void stopUpdateNotification()
    {
        mHandler.removeCallbacks(mStatusChecker);
    }

    public void startUpdateNotification()
    {
        mStatusChecker.run();
    }

    Runnable mStatusChecker = new Runnable() {
        @Override
        public void run() {
            nUpdate.execute();
            mHandler.postDelayed(mStatusChecker, MooGlobals.getInstance().getConfig().notificationTime);
        }
    };

    @Override
    protected void onDestroy() {
        mHandler.removeCallbacks(mStatusChecker);
        super.onDestroy();
    }

    @Override
    protected void onStop() {
        mHandler.removeCallbacks(mStatusChecker);
        super.onStop();
    }

    @Override
    protected void onPause() {
        mHandler.removeCallbacks(mStatusChecker);
        super.onPause();
    }

    @Override
    protected void onResume() {
        mStatusChecker.run();
        super.onResume();
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);

        // Get the notifications MenuItem and
        // its LayerDrawable (layer-list)
        MenuItem item = menu.findItem(R.id.action_notifications);

        // Update LayerDrawable's BadgeDrawable
        item.setIcon(buildCounterDrawable(mNotificationsCount, R.drawable.ic_bar_notifications));

        //init account menu
        MenuItem mAccount = (MenuItem)menu.findItem(R.id.action_account);
        SubMenu subAccount = mAccount.getSubMenu();
        int length = configApp.menuAccount.length();
        if (length > 0) {
            try {
                int i = 0;
                for (i = 0; i < configApp.menuAccount.length(); i++) {
                    JSONObject itemJson = configApp.menuAccount.getJSONObject(i);
                    hAccountMenus.put(itemJson.getString("key"), itemJson);
                    MenuItem item_account = subAccount.add(itemJson.getString("label"));
                    item_account.setTitleCondensed(itemJson.getString("key"));
                }
            } catch (Exception e) {

            }
        }

        MenuItem item_account = subAccount.add(getResources().getString(R.string.action_logout));
        item_account.setTitleCondensed("account_logout");
        return true;
    }
    private Drawable buildCounterDrawable(int count, int backgroundImageId) {
        LayoutInflater inflater = LayoutInflater.from(this);
        View view = inflater.inflate(R.layout.notification_counter, null);
        view.setBackgroundResource(backgroundImageId);

        if (count == 0) {
            View counterTextPanel = view.findViewById(R.id.counterValuePanel);
            counterTextPanel.setVisibility(View.GONE);
        } else {
            TextView textView = (TextView) view.findViewById(R.id.count);
            textView.setText("" + count);
        }

        view.measure(
                View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED),
                View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED));
        view.layout(0, 0, view.getMeasuredWidth(), view.getMeasuredHeight());

        view.setDrawingCacheEnabled(true);
        view.setDrawingCacheQuality(View.DRAWING_CACHE_QUALITY_HIGH);
        Bitmap bitmap = Bitmap.createBitmap(view.getDrawingCache());
        view.setDrawingCacheEnabled(false);

        return new BitmapDrawable(getResources(), bitmap);
    }
    public void updateNotificationsBadge(int count) {
        mNotificationsCount = count;

        // force the ActionBar to relayout its MenuItems.
        // onCreateOptionsMenu(Menu) will be called again.
        invalidateOptionsMenu();
    }
    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        if (intent.getExtras() != null) {
            if (intent.getExtras().get("url") != null) {
                loadUrl((String) intent.getExtras().get("url"));
            }

            //handle notification
            if (intent.getExtras().get("notification_url") != null) {
                loadUrl((String) intent.getExtras().get("notification_url"), true);
            }
        }
        //ab.collapseActionView();
        //now getIntent() should always return the last received intent
    }
    // XWalkView
    public String getUrlHost(){
        return configApp.urlHost;
    }
    public void showWebview() {
        mProgressBar.setVisibility(View.INVISIBLE);
        xWalkWebView.animate().alpha(1.0f)
                .setDuration(300)
                .setStartDelay(150);

        mProgressBar.animate().alpha(0.0f)
                .setDuration(60);
    }

    public void hideWebview() {
        mProgressBar.setAlpha(1.0f);
        mProgressBar.setVisibility(View.VISIBLE);
        //xWalkWebView.setAlpha(0.0f);
    }
    public void setXWalkView(XWalkView xWalkWebView)
    {
        this.xWalkWebView = xWalkWebView;
    }

    public void initXWalkView()
    {
        CookieSyncManager.createInstance(this);
        CookieSyncManager.getInstance().startSync();
        xWalkWebView.addJavascriptInterface(new UtilsWebViewJS(this),"Android");
        xWalkWebView.setUIClient(new XWalkUIClient(xWalkWebView){
            public void onPageLoadStarted(XWalkView view, String url) {
                loadingFinished = false;
                String key = url.replace("?access_token=" + getToken().getAccess_token() + "&language=" + getLanguageCode(), "");
                String url_full = key = key.replace(configApp.urlHost, "");
                String[] keys = key.split("/");
                if (keys.length < 2) {
                    key = "home";
                } else {
                    key = keys[1];
                }
                checkLogicUrl(key, url_full, false);

                hideWebview();
            }

            @Override
            public void onPageLoadStopped(XWalkView view, String url, LoadStatus status) {
                if (status == LoadStatus.FINISHED)
                {
                    bCheckNoLoadLogiUrl = false;
                    if (!url.equals("file:///android_asset/offline.html") && !sCurrentUrl.equals(url)) {
                        sCurrentUrl = "";
                    }
                    if (bCheckLoadUrl)
                    {
                        showWebview();
                    }

                    if (!redirect)
                    {
                        loadingFinished = true;
                    }

                    if (loadingFinished && !redirect)
                    {
                        showWebview();
                    }
                    else
                    {
                        redirect = false;
                    }
                }
                if (status == LoadStatus.FAILED)
                {
                    if (sCurrentUrl.isEmpty()) {
                        sCurrentUrl = xWalkWebView.getUrl();
                    }
                    xWalkWebView.load("file:///android_asset/offline.html",null);
                }
            }

            public void openFileChooser(XWalkView view, ValueCallback<Uri> uploadFile,
                                        String acceptType, String capture) {
                Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
                intent.addCategory(Intent.CATEGORY_OPENABLE);
                intent.setType("*/*");
                startActivityForResult(Intent.createChooser(intent, "File Chooser"), 0);
                mFilePathCallback = uploadFile;
            }
        });
        xWalkWebView.setResourceClient(new XWalkResourceClient(xWalkWebView) {
            public boolean shouldOverrideUrlLoading(XWalkView view, String url) {
                if (url.indexOf(configApp.urlHost) == -1)
                {
                    return false;
                }
                bCheckLoadUrl = true;
                if (url.indexOf("access_token") == -1)
                {
                    Integer index = url.indexOf("?");
                    if ( index == -1)
                    {
                        url += "?access_token=" + getToken().getAccess_token() + "&language=" + getLanguageCode();
                    }
                    else
                    {
                        url += "&access_token=" + getToken().getAccess_token() + "&language=" + getLanguageCode();
                    }
                    bCheckLoadUrl = false;
                    xWalkWebView.load(url, null);
                    if (!loadingFinished)
                    {
                        redirect = true;
                    }

                    loadingFinished = false;
                    return true;
                }

                return false;
            }
        });

        mProgressBar = (ProgressBar) findViewById(R.id.web_progress);

        //load default url
        Bundle extras = getIntent().getExtras();
        if (extras != null)
        {
            String sUrl = extras.getString("load_url");
            if (!sUrl.isEmpty())
            {
                loadUrl(sUrl);
            }
        }
        else
        {
            NotificationModel nNotification = MooGlobals.getInstance().getNotification();
            if (nNotification != null) {
                MooGlobals.getInstance().setNotification(null);
                if (!nNotification.getUrl().isEmpty()) {
                    loadUrl(nNotification.getUrl(), true);
                }
                else
                {
                    try {
                        loadUrl(configApp.urlHost + configApp.jListUrls.getString("home_everyone"));
                    } catch (Exception e) {

                    }
                }
            } else {
                try {
                    loadUrl(configApp.urlHost + configApp.jListUrls.getString("home_everyone"));
                } catch (Exception e) {

                }

            }
        }
    }
    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent intent)
    {
        super.onActivityResult(requestCode, resultCode, intent);

        if (xWalkWebView != null) {

            if (mFilePathCallback != null) {
                Uri result = intent == null || resultCode != Activity.RESULT_OK ? null
                        : intent.getData();
                if (result != null) {
                    try {
                        String path = MediaUtility.getPath(this, result);
                        Uri uri = Uri.fromFile(new File(path));
                        mFilePathCallback.onReceiveValue(uri);
                    }catch (Exception e)
                    {
                        mFilePathCallback.onReceiveValue(null);
                    }

                } else {
                    mFilePathCallback.onReceiveValue(null);
                }
            }

            mFilePathCallback = null;
        }
        xWalkWebView.onActivityResult(requestCode, resultCode, intent);
    }
    public void loadUrl(String url)
    {
        xWalkWebView.load(url + "?access_token=" + getToken().getAccess_token() + "&language="+getLanguageCode(), null);
    }

    public void loadUrl(String url,Boolean gmc_token)
    {
        xWalkWebView.load(url + "&access_token=" + getToken().getAccess_token() + "&language="+getLanguageCode(),null);
    }

    // End XWalkView
    //-----------------------------------------------------------------------

    // For NavigationView
    //-----------------------------------------------------------------------
    public void setDrawerLayout(DrawerLayout mDL){
        mDrawerLayout = mDL;
    }
    public void setNavigationView(NavigationView nav_view){
        this.navigationView = nav_view;
    }
    public NavigationView initNavigationView(){
        int length = configApp.menuItems.length();
        mMenus = (MenuBuilder) navigationView.getMenu();
        if (length > 0){
            try {
                int i = 0;
                for(i=0;i<configApp.menuItems.length();i++){
                    JSONObject itemJson = configApp.menuItems.getJSONObject(i);
                    hMenus.put(itemJson.getString("key"), itemJson);
                    MenuItem item = mMenus.add(R.id.group_menu,Menu.NONE,0,itemJson.getString("label"));
                    item.setTitleCondensed(itemJson.getString("key"));
                    if (!itemJson.getString("icon").isEmpty()) {
                        item.setIcon(getResources().getIdentifier(itemJson.getString("icon"), "drawable", this.getPackageName()));
                    }
                    item.setCheckable(true);
                }
            }catch (Exception e)
            {

            }
        }

        length = configApp.pages.length();
        //add menu setting
        MenuItem item = mMenus.add(R.id.group_menu_add,Menu.NONE,0,getResources().getString(R.string.text_setting));
        item.setTitleCondensed("setting");
        item.setIcon(getResources().getIdentifier("ic_setting", "drawable", this.getPackageName()));
        item.setCheckable(true);

        if (length > 0){
            try {
                int i = 0;
                for(i=0;i<configApp.pages.length();i++){
                    JSONObject itemJson = configApp.pages.getJSONObject(i);
                    hMenus.put(itemJson.getString("key"), itemJson);
                    item = mMenus.add(R.id.group_menu_add,Menu.NONE,0,itemJson.getString("label"));
                    item.setTitleCondensed(itemJson.getString("key"));
                    if (!itemJson.getString("icon").isEmpty()) {
                        item.setIcon(getResources().getIdentifier(itemJson.getString("icon"), "drawable", this.getPackageName()));
                    }
                    item.setCheckable(true);
                }
            }catch (Exception e)
            {

            }
        }

        /*
        if (navigationView != null) {
            setupDrawerContent(navigationView);
        }
        */
        return navigationView;
    }

    public void openDrawer(){
        if(mDrawerLayout != null){
            mDrawerLayout.openDrawer(GravityCompat.START);
        }
    }
    public void closeDrawer(){
        if(mDrawerLayout != null){
            mDrawerLayout.closeDrawers();
        }
    }
	public void checkMenuHide()
    {
        Me mMe = MooGlobals.getInstance().getMe();
        ArrayList aMenus = null;
        if (mMe != null)
        {
            aMenus = (ArrayList)mMe.getMenus();
        }

        if (aMenus == null || aMenus.size() == 0)
        {
            return;
        }

        mMenus = (MenuBuilder) navigationView.getMenu();
        int i;
        int size = mMenus.size();
        ArrayList<Integer> aIdRemove = new ArrayList<Integer>();
        for (i = 0;i<size;i++)
        {
            MenuItem mItem = mMenus.getItem(i);
            for (int j = 0; j<aMenus.size();j++)
            {
                LinkedTreeMap menu = (LinkedTreeMap)aMenus.get(j);
                String name = (String)menu.get("name");
                String value = (String)menu.get("value");
                if (mItem.getTitleCondensed().toString().indexOf(name) != -1 && value.isEmpty())
                {
                    mItem.setVisible(false);
                    break;
                }
            }
        }
    }
    // End NavigationView
    //-----------------------------------------------------------------------


    // For spin
    //-----------------------------------------------------------------------------
    public  void setSpinner(MooSpinner mSpinner){
        this.mSpinner = mSpinner;
    }
    public void initSpinner(){
        mSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parentView, View selectedItemView, int position, long id) {
                // your code here
                if (bFirstSpin == 0) {
                    MenuBarItem item = (MenuBarItem) parentView.getItemAtPosition(position);
                    bCheckNoLoadLogiUrl = true;
                    loadUrl(configApp.urlHost + item.getUrl());
                }
                else {
                    bFirstSpin--;
                }
            }
            @Override
            public void onNothingSelected(AdapterView<?> parentView) {
                // your code here
            }
        });
    }
    // End spin
    //------------------------------------------------------------------------------

    // For actionbar
    //-------------------------------------------------------------------------------
    public void setActionBar(ActionBar ab){
        this.ab = ab;
    }
    public void initActionBar(){
        ab.setHomeAsUpIndicator(R.drawable.ic_menu);
        ab.setDisplayHomeAsUpEnabled(true);
        ab.setDisplayShowTitleEnabled(true);
        ab.setTitle(getResources().getString(R.string.toolbar_title_home));
    }
    // End actionbar
    //--------------------------------------------------------------------------------
	
	// For menu
    //----------------------------------------------------------------------------------
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        String key = (String)item.getTitleCondensed();
        if (!key.isEmpty())
        {
            switch (key)
            {
                case "account_logout":
                    basePresenter.onClickLogout();
                    break;
                /*case "account_profile":
                    basePresenter.onClickProfileInformation();
                    break;
                case "account_picture":
                    basePresenter.onClickChangeProfilePiture();
                    break;
                case "account_invite":
                    basePresenter.onClickInviteFriends();
                    break;*/
                default:
                    JSONObject oMenu = hAccountMenus.get(key);
                    if (oMenu != null) {
                        try {
                            loadUrl(configApp.urlHost + oMenu.getString("url"));
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                    break;
            }
        }
        int id = item.getItemId();
        switch (id) {
            case R.id.action_notifications:
                Intent iNotification = new Intent(this, NotificationActivity.class);
                startActivity(iNotification);
                return true;
            case R.id.action_search:
                Intent iSearch = new Intent(this, SearchResultsActivity.class);
                startActivity(iSearch);
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
    // End menu
    //----------------------------------------------------------------------------------

    // Avatar
    //----------------------------------------------------------------------------------
    public void initAvatar(ImageView mImageView, NetworkImageView mCoverView, TextView tAccountName){ // (ImageView) findViewById(R.id.myImage);
        loadingOwerAvatar = new LoadingOwnerAvatar((MooApplication) this.getApplication(),this);
        loadingOwerAvatar.setmImageView(mImageView);
        loadingOwerAvatar.setmCoverView(mCoverView);
        loadingOwerAvatar.SettAccountName(tAccountName);
        loadingOwerAvatar.execute();
    }
    //----------------------------------------------------------------------------------
    // End avatar

    // Tab logic
    //----------------------------------------------------------------------------------
    public void checkLogicUrl(String key,String url_full,Boolean bLoad)
    {
        if (key.indexOf("?") != -1)
        {
            String tmp = key.substring(key.indexOf("?"),key.length());
            key = key.replace(tmp,"");
        }
        if (bCheckNoLoadLogiUrl) {
            if (!bLoad)
                return;
        }
        if (key.equals("activities"))
            key = "home";

        if (key.equals("home")) {
            tabLayout.setVisibility(View.VISIBLE);
            try {
                if (url_full.equals(configApp.jListUrls.getString("home_friend"))) {
                    tabLayout.getTabAt(1).select();
                }else {
                    tabLayout.getTabAt(0).select();
                }
            }catch (Exception e){

            }
        } else {
            tabLayout.setVisibility(View.GONE);
        }
        String text = "";
        for(Map.Entry<String, JSONObject> entry : hMenus.entrySet()) {
            String text_key = entry.getKey();
            JSONObject value = entry.getValue();
            if (text_key.indexOf(key) >= 0)
            {
                text = text_key;
                break;
            }
        }
        if (!text.isEmpty()) {
            try {
                //Init menu
                mMenus = (MenuBuilder) navigationView.getMenu();
                int i;
                for (i = 0;i<mMenus.size();i++)
                {
                    MenuItem mItem = mMenus.getItem(i);
                    if (key.equals(mItem.getTitleCondensed()))
                    {
                        if (prevMenuItem != null) {
                            prevMenuItem.setChecked(false);
                        }
                        prevMenuItem = mItem;
                        mItem.setChecked(true);
                        break;
                    }
                }

                JSONObject oMenu = hMenus.get(text);
                JSONArray submenu = oMenu.getJSONArray("subLinks");
                if (submenu.length() > 0) {
                    bFirstSpin++;
                    mSpinner.setVisibility(View.VISIBLE);
                    ab.setDisplayShowTitleEnabled(false);
                    List<MenuBarItem> spinnerItems = new ArrayList<MenuBarItem>();
                    for (i = 0; i < submenu.length(); i++) {
                        JSONObject itemJson = submenu.getJSONObject(i);
                        spinnerItems.add(new MenuBarItem(itemJson.getString("label"), itemJson.getString("url"), oMenu.getString("label")));
                    }

                    MenuBarSpinnerAdapter adapter = new MenuBarSpinnerAdapter(ab.getThemedContext(), spinnerItems);
                    mSpinner.setAdapter(adapter);
                } else {
                    mSpinner.setVisibility(View.GONE);
                    ab.setDisplayShowTitleEnabled(true);
                    ab.setTitle(oMenu.getString("label"));
                }
                if (bLoad)
                {
                    try {
                        loadUrl(configApp.urlHost + oMenu.getString("url"));
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        else
        {
            switch (key)
            {
                case "conversations":
                    mSpinner.setVisibility(View.GONE);
                    ab.setDisplayShowTitleEnabled(true);
                    ab.setTitle(getResources().getString(R.string.toolbar_title_message));
                    break;
                default:
                    mSpinner.setVisibility(View.GONE);
                    ab.setDisplayShowTitleEnabled(true);
                    ab.setTitle(getResources().getString(R.string.toolbar_title_home));
                    break;
            }
            if (prevMenuItem != null) {
                prevMenuItem.setChecked(false);
            }
            prevMenuItem = null;
        }
    }
    // End Tab
	public void showFullAds()
    {
        if (MooGlobals.getInstance().getsAdmodInterstitialId().isEmpty() || !MooGlobals.getInstance().getbAdFull())
        {
            return;
        }
        // Create the InterstitialAd and set the adUnitId.
        final InterstitialAd mInterstitialAd = new InterstitialAd(this);
        // Defined in res/values/strings.xml
        mInterstitialAd.setAdUnitId(MooGlobals.getInstance().getsAdmodInterstitialId());

        AdRequest adRequest = new AdRequest.Builder().build();

        mInterstitialAd.loadAd(adRequest);

        mInterstitialAd.setAdListener(new AdListener() {
            public void onAdLoaded() {
                mInterstitialAd.show();
            }
        });
    }
}
