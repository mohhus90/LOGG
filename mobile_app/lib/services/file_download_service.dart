import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';

import 'api_client.dart';

class FileDownloadService {
  /// Fetches raw bytes from an authenticated employee-API path, for callers
  /// that render the file themselves (e.g. the in-app PDF viewer).
  static Future<List<int>> fetchBytes(String path) async {
    final response = await ApiClient.instance.dio.get(
      path,
      options: Options(responseType: ResponseType.bytes),
    );
    return response.data;
  }

  /// Downloads a file (non-PDF documents: images, Office files, ...) from an
  /// authenticated employee-API path and opens it with the device's default
  /// viewer, since there's no single bundled renderer for arbitrary formats.
  static Future<String?> downloadAndOpen(String path, String fileName) async {
    try {
      final response = await ApiClient.instance.dio.get(
        path,
        options: Options(responseType: ResponseType.bytes),
      );

      final dir = await getTemporaryDirectory();
      final file = File('${dir.path}/$fileName');
      await file.writeAsBytes(response.data);

      final result = await OpenFilex.open(file.path);
      if (result.type.name != 'done') {
        return 'تعذر فتح الملف على هذا الجهاز';
      }
      return null;
    } catch (e) {
      return ApiClient.errorMessage(e);
    }
  }
}
