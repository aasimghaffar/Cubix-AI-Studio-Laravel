<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\Package;
use App\Models\SitePage;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/** Public pages of the Blade frontend. */
class SiteController extends Controller
{
    public function home()
    {
        return view('home', [
            'tools'        => AiTool::where('status', 'active')->orderBy('sort_order')->get(),
            'packages'     => $this->packages(),
            'testimonials' => Testimonial::where('enabled', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function tools()
    {
        return view('tools', [
            'tools'    => AiTool::where('status', 'active')->orderBy('sort_order')->get(),
            'packages' => $this->packages(), // the access-gate popup shows live plans
        ]);
    }

    public function workspace(string $slug)
    {
        $tool = AiTool::where('slug', $slug)->where('status', 'active')->firstOrFail();

        return view('workspace', [
            'tool'     => $tool,
            'packages' => $this->packages(), // the access-gate popup shows live plans
        ]);
    }

    public function pricing()
    {
        return view('pricing', ['packages' => $this->packages()]);
    }

    public function contact()
    {
        return view('contact');
    }

    /** /p/{slug} — admin-managed pages, honouring the layout width setting. */
    public function page(string $slug)
    {
        $page = SitePage::where('slug', $slug)->where('published', true)
            ->firstOrFail(['slug', 'title', 'content', 'layout']);

        // Shortcode partials need live data
        return view('page', [
            'page'     => $page,
            'packages' => $this->packages(),
            'tools'    => AiTool::where('status', 'active')->orderBy('sort_order')->get(),
        ]);
    }

    /** /lang/{code} — persist the language choice and go back. */
    public function setLanguage(string $code)
    {
        // Only accept codes that actually exist and are enabled.
        $valid = collect(webctx()->languages())->pluck('code')->contains($code);

        return redirect()->back()->withCookie(
            Cookie::make('lang', $valid ? $code : 'en', 60 * 24 * 365, '/', null, false, false)
        );
    }

    /** POST /pricing/request-custom — lands in the admin Messages inbox. */
    public function requestCustom(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|min:20|max:2000']);

        \App\Models\ContactMessage::create([
            'name'    => $request->user()->name,
            'email'   => $request->user()->email,
            'subject' => 'Custom package request',
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return response()->json(['message' => "Request sent! We'll get back to you by email with a tailored plan."]);
    }

    /** Same visibility rules as GET /api/packages. */
    protected function packages()
    {
        $user = auth()->user();

        return Package::where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('is_custom', false);
                if ($user) {
                    $q->orWhere(fn ($qq) => $qq->where('is_custom', true)->where('user_id', $user->id));
                }
            })
            ->orderBy('price')
            ->get();
    }
}
