<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Services\ScannerApi;
use App\Services\UrlHash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UrlshortenerController extends Controller
{
    protected $hash;
    protected $scannerApi;

    public function __construct(UrlHash $hash, ScannerApi $scannerApi)
    {
        $this->scannerApi = $scannerApi;
        $this->hash = $hash;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $urls = Url::all()->toArray();

        return array_reverse($urls);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url|unique:urls,old_url',
        ]);

        $old_url = $request->input('url');

        $hash = $this->hash->createUrlHash($old_url);

        $host = parse_url($old_url, PHP_URL_HOST);
        $tld_pattern = '/\.(com|io|org|net|edu|gov|mil|co|us|uk|ca|de|jp|fr|au|ru|ch|it|nl|se|no|es|mil)$/';

        $host = preg_replace($tld_pattern, '', $host);

        $url = new Url([
            'hash' => $hash,
            'old_url' => $old_url,
            'new_url' => config('app.url') ."/$host" ."/$hash",
        ]);

        $response = $this->scannerApi->postScanUrl($old_url);

        if ($response['WebsiteHttpResponseCode'] != 200) {
            throw ValidationException::withMessages(['scan' => 'Malicious URL Detected!']);
        }

        $url->save();

        return response()->json('URL Created Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $url = Url::find($id);

        $url->delete();

        return response()->json('URL Deleted Successfully.');
    }

    /**
     * Handle the URL shortener link.
     *
     * @param Request $request
     * @return redirect
     */
    // public function handle(Request $request)
    // {
    //     $uri = substr($_SERVER["REQUEST_URI"], 1);
    //     echo $uri;
    //     $url = Url::Where('hash', $uri)->get('old_url');
    //     echo $url;
    //     if ($uri == '' || $url == '' || count($url) == 0) {
    //         return abort(403);
    //     } else {
    //         return redirect($url[0]['old_url']);
    //     }
    // }
    public function handle($subfolder, $hash)
    {
        // You can now use both $subfolder and $hash to determine the right action
        // For example, logging or performing actions based on subfolder

        // Look up the original URL ussing the hash
        $url = Url::where('hash', $hash)->first();

        if ($url) {
            return redirect($url->old_url);
        } else {
            return abort(404);
        }
    }
}
