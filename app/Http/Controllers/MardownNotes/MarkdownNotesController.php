<?php

namespace App\Http\Controllers\MardownNotes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarkdownNotesController extends Controller
{
    public function create()
    {

        return view('markdown-notes.create');
    }
}
