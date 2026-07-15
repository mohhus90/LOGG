import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:printing/printing.dart';

/// Renders PDF bytes with the bundled PDFium engine instead of handing the
/// file to whatever external app the OS picks - some third-party PDF viewers
/// don't handle dompdf's Arabic/RTL text encoding correctly, even though the
/// PDF itself is valid (Chrome and PDFium render it fine).
class PdfViewerScreen extends StatelessWidget {
  final String title;
  final List<int> bytes;
  final String fileName;

  const PdfViewerScreen({super.key, required this.title, required this.bytes, required this.fileName});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: PdfPreview(
        build: (format) => Uint8List.fromList(bytes),
        canChangeOrientation: false,
        canChangePageFormat: false,
        canDebug: false,
        allowSharing: true,
        allowPrinting: true,
        pdfFileName: fileName,
      ),
    );
  }
}
