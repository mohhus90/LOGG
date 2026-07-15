import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';

import 'api_client.dart';

class FileDownloadService {
  /// Downloads a PDF (or any file) from an authenticated employee-API path
  /// and opens it with the device's default viewer.
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
