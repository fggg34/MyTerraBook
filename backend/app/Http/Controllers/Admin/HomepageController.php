<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageController extends Controller
{
    /** @var array<string, string> */
    private array $sectionLabels = [
        'topbar' => 'Top Banner',
        'header' => 'Header / Nav',
        'hero' => 'Hero Section',
        'trust' => 'Trust Strip',
        'rent' => 'What We Rent',
        'why' => 'Why MyTerra',
        'footer' => 'Footer',
    ];

    public function index(): View
    {
        $sections = HomepageSection::query()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        return view('admin.homepage.index', [
            'sections' => $sections,
            'sectionLabels' => $this->sectionLabels,
        ]);
    }

    public function edit(string $section): View
    {
        $record = HomepageSection::query()
            ->where('section_key', $section)
            ->firstOrFail();

        return view('admin.homepage.edit', [
            'section' => $record,
            'sectionLabel' => $this->sectionLabels[$section] ?? ucfirst($section),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $record = HomepageSection::query()
            ->where('section_key', $section)
            ->firstOrFail();

        $content = $request->input('content', []);
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $content = is_array($decoded) ? $decoded : [];
        }

        $record->update([
            'content' => $this->mergeContent($record->content ?? [], $content),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Section updated successfully.');
    }

    public function uploadImage(Request $request, string $section): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'field' => ['required', 'string', 'max:120'],
        ]);

        $record = HomepageSection::query()
            ->where('section_key', $section)
            ->firstOrFail();

        $path = $request->file('image')->store('homepage', 'public');
        $content = $record->content ?? [];

        if ($request->filled('card_index')) {
            $index = (int) $request->input('card_index');
            $cards = $content['cards'] ?? [];
            if (isset($cards[$index])) {
                $cards[$index]['image'] = $path;
                $content['cards'] = $cards;
            }
        } elseif ($request->filled('field')) {
            $content[$request->input('field')] = $path;
        }

        $record->update(['content' => $content]);

        return back()->with('success', 'Image uploaded successfully.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['string'],
        ]);

        foreach ($request->input('order', []) as $sortOrder => $sectionKey) {
            HomepageSection::query()
                ->where('section_key', $sectionKey)
                ->update(['sort_order' => (int) $sortOrder]);
        }

        return back()->with('success', 'Section order updated.');
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    private function mergeContent(array $existing, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if (is_array($value) && isset($existing[$key]) && is_array($existing[$key])) {
                if (array_is_list($value)) {
                    $existing[$key] = $value;
                } else {
                    $existing[$key] = $this->mergeContent($existing[$key], $value);
                }
            } else {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }
}
