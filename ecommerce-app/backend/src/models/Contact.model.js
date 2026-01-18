import mongoose from 'mongoose';

const contactSchema = new mongoose.Schema({
  name: {
    type: String,
    required: [true, 'Ju lutem vendosni emrin'],
    trim: true,
    maxlength: [100, 'Emri nuk mund të jetë më i gjatë se 100 karaktere']
  },
  email: {
    type: String,
    required: [true, 'Ju lutem vendosni email-in'],
    match: [
      /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/,
      'Ju lutem vendosni një email valid'
    ]
  },
  subject: {
    type: String,
    required: [true, 'Ju lutem vendosni subjektin'],
    trim: true,
    maxlength: [200, 'Subjekti nuk mund të jetë më i gjatë se 200 karaktere']
  },
  message: {
    type: String,
    required: [true, 'Ju lutem shkruani mesazhin'],
    maxlength: [1000, 'Mesazhi nuk mund të jetë më i gjatë se 1000 karaktere']
  },
  isRead: {
    type: Boolean,
    default: false
  },
  repliedAt: Date,
  reply: String
}, {
  timestamps: true
});

// Indexes
contactSchema.index({ createdAt: -1 });
contactSchema.index({ isRead: 1 });

const Contact = mongoose.model('Contact', contactSchema);

export default Contact;
