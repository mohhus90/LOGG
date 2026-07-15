import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';

/// Renders PDF bytes with a bundled PDFium library (flutter_pdfview /
/// android-pdf-viewer), NOT Android's OS-level android.graphics.pdf.PdfRenderer.
/// That OS renderer (used by the `printing` and `pdfx` packages) has weaker
/// Arabic/RTL bidi handling and showed dompdf-generated Arabic text reversed
/// even though the same PDF renders correctly on desktop/Chrome.
class PdfViewerScreen extends StatelessWidget {
  final String title;
  final List<int> bytes;
  final String fileName;

  const PdfViewerScreen({super.key, required this.title, required this.bytes, required this.fileName});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: PDFView(
        pdfData: Uint8List.fromList(bytes),
        enableSwipe: true,
        swipeHorizontal: false,
        autoSpacing: true,
        pageFling: true,
      ),
    );
  }
}
